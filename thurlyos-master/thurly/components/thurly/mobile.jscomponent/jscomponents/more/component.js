

var items = [];
var sections = [];
var SITE_ID = BX.componentParameters.get("SITE_ID", "s1");
BX.listeners = {};




result.menu.forEach(
	sec =>
	{
		if(
			sec.min_api_version && sec.min_api_version > Application.getApiVersion()
			|| sec.hidden == true
		)
			return;

		var sectionCode = "section_"+sec.sort;
		var sectionItems = sec.items.map(item =>
		{
			if(item.hidden)
			{
				return false;
			}

			item.sectionCode = sectionCode;
			if(item.attrs)
			{
				item.params = item.attrs;
				delete item.attrs;
			}

			return item;
		});

		sections.push({
			title:sec.title,
			id: sectionCode
		});

		items = items.concat(sectionItems)
	});

/**
 * @var  BaseList menu
 */

var More = {
	findIn:function(items, query){
		var query = query.toUpperCase();
		var searchResult = items.filter(item =>
		{
			var section = sections.find(section => section.id === item.sectionCode);
			if(item.title && item.title.toUpperCase().indexOf(query)>=0|| section&& section.title && section.title.toUpperCase().indexOf(query)>=0)
				return item;
		})
			.map(item => {
				var section = sections.find(section => section.id === item.sectionCode);
				item.subtitle = section ? section.title: "";
				item.useLetterImage = true;
				return item;
			});

		return searchResult;
	},
	find:function(query){
		var result = this.findIn(items, query);
		var groupItems = [];
		items.forEach( item=> {
			if(item.type === "group")
			{
				var section = sections.find(section => section.id === item.sectionCode);
				groupItems = groupItems.concat(this.findIn(item.params.items, query)
					.map(groupItem => {
							groupItem.subtitle = section.title+" -> "+item.title;
							return groupItem;
						}
					));
			}
		});

		return result.concat(groupItems);
	},
	updateCounters:function(siteCounters){

		var counters = Object.keys(siteCounters);
		var updateCountersData = counters.filter(counter => this.counterList.includes(counter))
			.map(counter => {
				return {filter:{"params.counter": counter},  element:{messageCount:siteCounters[counter]}}
			});

		if(updateCountersData.length > 0)
		{
			menu.updateItems(updateCountersData);
		}
	},
	init:function(){
		menu.setListener(this.listener);
		items = items.filter((item) => item!=false).map((item)=>{
			if(item.type != "destruct")
			{
				item.styles =
					{
						title:{
							color: "#FF4E5665"
						}
					};
			}

			if(item.params.counter)
			{
				this.counterList.push(item.params.counter);
			}

			return item;

		});


		BX.addCustomEvent("onPullEvent-main", (command, params)=>
		{
			console.log(params);
			if (command == "user_counter")
			{
				if(params[SITE_ID])
					this.updateCounters(params[SITE_ID])
			}
		});

		BX.addCustomEvent("onUpdateUserCounters", (data)=>
		{
			console.log(data);
			if(data[SITE_ID])
				this.updateCounters(data[SITE_ID])
		});

		BX.onViewLoaded(()=> {
			menu.setItems(items, sections);
			setTimeout(()=>{
				var cachedCounters = Application.sharedStorage().get('userCounters');
				if(cachedCounters)
				{
					try
					{
						var counters = JSON.parse(cachedCounters);
						if(counters[SITE_ID])
							this.updateCounters(counters[SITE_ID]);

					}
					catch (e)
					{
						//do nothing
					}
				}
			}, 300)

		});
	},
	listener:function(eventName, data)
	{
		var item = null;
		if(eventName === "onUserTypeText")
		{
			if(data.text.length > 0)
				menu.setSearchResultItems(More.find(data.text), []);
			else
				menu.setSearchResultItems([], []);
		}
		else if(eventName === "onItemAction")
		{
			item = data.item;
			if(item.params.actionOnclick)
				eval(item.params.actionOnclick);
		}
		else if(eventName === "onItemSelected" || eventName === "onSearchItemSelected" )
		{
			item = data;
			if(item.type === "group")
			{
				PageManager.openComponent("JSComponentSimpleList", {
					title: item.title,
					params:{
						items:item.params.items
					}
				})
			}
			else if(item.params.onclick)
			{
				eval(item.params.onclick);
			}
			else if(item.params.action)
			{
				Application.exit();
			}
			else if(item.params.url)
			{
				if(item.params._type && item.params._type === "list")
				{
					PageManager.openList(item.params);
				}
				else
				{
					PageManager.openPage({url:item.params.url, title:item.title});
				}
			}
		}
		else if(eventName === "onRefresh")
		{
			reloadAllScripts();
			menu.stopRefreshing();
		}
	},
	counterList:[]
};

More.init();
