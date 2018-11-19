"use strict";
/**
 * @bxjs_lang_path component.php
 */

var REVISION = 8; // api revision - check module/im/include.php

/* Clean session variables after page restart */
if (typeof clearInterval == 'undefined')
{
	clearInterval = (id) => clearTimeout(id);
}
if (typeof RecentList != 'undefined' && typeof RecentList.cleaner != 'undefined')
{
	RecentList.cleaner();
}

/* Recent list API */
var RecentList = {};

RecentList.init = function(params)
{
	params = params || {};

	/* set cross-links in class */
	let links = ['base', 'cache', 'convert', 'pull', 'push', 'queue', 'timer', 'notify', 'event', 'action', 'search'];
	links.forEach((subClass) => {
		if (typeof this[subClass] != 'undefined')
		{
			links.forEach((element) => {
				if (element == 'base')
				{
					this[subClass]['base'] = this;
				}
				else if (subClass != element)
				{
					this[subClass][element] = this[element];
				}
			});
		}
	});

	let configMessages = BX.componentParameters.get("MESSAGES", {});
	for (let messageId in configMessages)
	{
		if (configMessages.hasOwnProperty(messageId))
		{
			BX.message[messageId] = configMessages[messageId];
		}
	}

	/* vars */
	this.debugLog = BX.componentParameters.get('PULL_DEBUG', false);
	this.generalChatId = BX.componentParameters.get('IM_GENERAL_CHAT_ID', 0);

	this.imagePath = component.path+'images';

	this.list = [];
	this.listEmpty = true;
	this.blocked = {};

	this.userId = parseInt(BX.componentParameters.get('USER_ID', 0));
	this.userData = {};

	this.colleaguesList = [];

	this.siteId = BX.componentParameters.get('SITE_ID', 's1');
	this.languageId = BX.componentParameters.get('LANGUAGE_ID', 'en');

	this.messageCount = 0;
	this.messageCountArray = {};

	this.listRequestAfterErrorInterval = 10000;
	this.updateCounterInterval = 1000;

	this.loadingFlag = true;

	this.request = {};

	this.cache.database = new ReactDatabase("im_recent_"+(this.isRecent()? 'base': 'ol'), this.userId, this.languageId);

	/* events */

	BX.addCustomEvent("failRestoreConnection", () =>
	{
		BX.onViewLoaded(this.refresh.bind(this));
	});

	/* start */
	BX.onViewLoaded(() =>
	{
		this.dialogOptionInit();

		this.action.init();
		this.push.init();
		this.event.init();
		this.search.init();

		if (this.isRecent())
		{
			this.notify.init();
		}

		this.timer.init();
		this.queue.init();
		this.cache.init();
		this.pull.init();

		this.refresh({start: true});
	});

	BX.addCustomEvent("onImDetailShowed", (data) =>
	{
		this.updateElement(data.dialogId, {
			counter: 0
		});

		if (!this.push.history[data.dialogId])
		{
			return false;
		}

		let params = this.push.getOpenDialogParams(data.dialogId);
		params.logAction = "onImDetailShowed";

		if (this.isRecent())
		{
			if (params.type == 'chat' && params.chat.type == 'lines' && this.isOpenlinesOperator())
			{
				return false;
			}
		}
		else if (this.isOpenlinesRecent())
		{
			if (params.type != 'chat' || params.chat.type != 'lines')
			{
				return false;
			}
		}

		BX.postWebEvent("onPageParamsChangedLegacy", {
			"url" : "/mobile/im/dialog.php",
			"data" : params
		});
	});

	BX.addCustomEvent("onAppActiveBefore", () =>
	{
		BX.onViewLoaded(() =>
		{
			this.refresh();
		});
	});
	BX.addCustomEvent("onAppActive", () =>
	{
		this.push.actionExecute();
	});

	this.push.actionExecute();

	return true;
};

RecentList.openDialog = function(dialogId, waitHistory)
{
	clearTimeout(this.openDialogTimeout);
	if (waitHistory === true)
	{
		let history = this.push.manager.get();
		if (typeof history != "undefined" && Object.keys(history).length > 0)
		{
			this.openDialogTimeout = setTimeout(() =>{
				this.openDialog(dialogId, true);
			}, 300);

			return true;
		}
	}

	let data = this.push.getOpenDialogParams(dialogId);

	let pageParams = {
		data : data,
		unique : true,
		url : "/mobile/im/dialog.php"
	};

	BX.postWebEvent("onPageParamsChangedLegacy", {
		"url" : "/mobile/im/dialog.php",
		"data" : data
	});
	PageManager.openPage(pageParams);

	return true;
};

RecentList.callUser = function(userId, action, number)
{
	let element = this.getElement(userId);
	if (!element)
	{
		return false;
	}

	let userData = {};
	userData[this.userId] = this.userData;
	userData[element.id] = element.user;

	if (typeof action == 'undefined')
	{
		// TODO context menu calls
		BX.postComponentEvent("onCallInvite", [{userId: element.id, video: false, userData: userData}], "calls");
	}
	else
	{
		if (action == 'video')
		{
			BX.postComponentEvent("onCallInvite", [{userId: element.id, video: true, userData: userData}], "calls");
		}
		else if (action == 'phone')
		{
			BX.postComponentEvent("onPhoneTo", [{number: number, userData: userData}], "calls");
		}
		else
		{
			BX.postComponentEvent("onCallInvite", [{userId: element.id, video: false, userData: userData}], "calls");
		}
	}
};

RecentList.updateCounter = function(delay)
{
	if (delay !== false)
	{
		if (!this.updateCounterTimeout)
		{
			this.updateCounterTimeout = setTimeout(() => this.updateCounter(false), this.updateCounterInterval);
		}
		return true;
	}
	clearTimeout(this.updateCounterTimeout);
	this.updateCounterTimeout = null;

	this.messageCount = 0;
	this.messageGroupCount = [];
	for (let entityId in this.messageCountArray)
	{
		if (this.messageCountArray.hasOwnProperty(entityId))
		{
			if (this.messageGroupCount.indexOf(entityId) == -1 && this.messageCountArray[entityId] > 0)
			{
				this.messageGroupCount.push(entityId);
			}
			this.messageCount += this.messageCountArray[entityId];
		}
	}

	for (let dialogId in this.push.history)
	{
		if (!this.push.history.hasOwnProperty(dialogId))
		{
			continue;
		}

		if (typeof this.messageCountArray[dialogId] == 'undefined' || this.messageCountArray[dialogId] == 0)
		{
			delete this.push.history[dialogId];
		}
	}

	if (this.isRecent())
	{
		BX.postComponentEvent("onUpdateBadges", [{
			'messages' : this.messageCount,
			'notifications' : this.notify.counter
		}, true], "communication");
	}
	else
	{
		BX.postComponentEvent("onUpdateBadges", [{
			'openlines' : this.messageCount,
		}, true], "communication");
	}
};

RecentList.isRecent = function()
{
	return BX.componentParameters.get('COMPONENT_CODE') == "im.recent";
};

RecentList.isOpenlinesRecent = function()
{
	return BX.componentParameters.get('COMPONENT_CODE') == "im.openlines.recent";
};

RecentList.isOpenlinesOperator = function()
{
	return BX.componentParameters.get('OPENLINES_USER_IS_OPERATOR', false);
};

RecentList.cleaner = function()
{
	BX.listeners = {};

	this.timer.destroy();
	this.queue.destroy();

	console.warn('RecentList.cleaner: OK');
};

RecentList.checkRevision = function(newRevision)
{
	if (typeof(newRevision) != "number" || REVISION >= newRevision)
	{
		return true;
	}

	console.warn('RecentList.checkRevision: reload scripts because revision up ('+REVISION+' -> '+newRevision+')');
	reloadAllScripts();

	return false;
};

RecentList.dialogOptionInit = function()
{
	if (this.isRecent())
	{
		dialogList.setSections([
			{title : '', id : "pinned", backgroundColor: "#ffffff", sortItemParams:{order: "desc"}},
			{title : '', id : "general", backgroundColor: "#ffffff", sortItemParams:{order: "desc"}}
		]);
	}
	else
	{
		dialogList.setSections([
			{title : '', id : "general", backgroundColor: "#ffffff", sortItemParams:{order: "asc"}},
			{title : BX.message("OL_SECTION_PIN"), id : "pinned", backgroundColor: "#f6f6f6", sortItemParams:{order: "asc"}},
			{title : BX.message("OL_SECTION_WORK"), id : "work", backgroundColor: "#ffffff", styles : { title: {color:"#e66467"}}, sortItemParams:{order: "asc"}},
			{title : BX.message("OL_SECTION_ANSWERED"), id : "answered", backgroundColor: "#ffffff", styles: { title : {color:"#6EA44E"}}, sortItemParams:{order: "desc"}}
		]);
	}

	return true;
};

RecentList.refresh = function(params)
{
	params = params || {start: false};

	clearTimeout(this.refreshTimeout);

	let recentParams = {};
	if (this.isRecent())
	{
		if (this.isOpenlinesOperator())
		{
			recentParams['SKIP_OPENLINES'] = 'Y';
		}
	}
	else if (this.isOpenlinesRecent())
	{
		if (this.isOpenlinesOperator())
		{
			recentParams['SKIP_CHAT'] = 'Y';
			recentParams['SKIP_DIALOG'] = 'Y';
		}
	}
	this.requestAbort('refresh');
	console.info("RecentList.refresh: send request to server", recentParams);

	let requestMethods = {
		serverTime: ['server.time'],
		revision: ['im.revision.get'],
		recent: ['im.recent.get', recentParams],
		counters: ['im.counters.get', {}],
	};

	if (params.start)
	{
		requestMethods.userData = ['im.user.get'];
	}

	if (this.isRecent())
	{
		requestMethods.userCounters = ['user.counters'];
		requestMethods.lastSearch = ['im.search.last.get'];
		if (params.start)
		{
			requestMethods.colleagues = ['im.department.colleagues.get', {'USER_DATA': 'Y', 'LIMIT': 50}];
		}
	}

	this.timer.start('recent', 'load', 3000, () => {
		this.loadingFlag = true;
		dialogList.setTitle({text: BX.message('IM_REFRESH_TITLE'), useProgress:true});
		console.warn("RecentList.refresh: slow connection show progress icon");
	});

	let executeTime = new Date();
	BX.rest.callBatch(requestMethods, (result) =>
	{
		this.requestUnregister('refresh');
		this.timer.stop('recent', 'load', true);

		if (this.loadingFlag)
		{
			dialogList.setTitle({text: BX.message('COMPONENT_TITLE'), useProgress:false});
			this.loadingFlag = false;
		}

		let revisionError = result.revision.error();
		let serverTimeError = result.serverTime.error();
		let recentError = result.recent.error();
		let countersError = result.counters.error();
		let userDataError = params.start? result.userData.error(): false;
		let userCountersError = this.isRecent()? result.userCounters.error(): false;
		let lastSearchError = this.isRecent()? result.lastSearch.error(): false;
		let colleaguesError = this.isRecent() && params.start? result.colleagues.error(): false;

		// revision block
		if (result.revision && !revisionError)
		{
			let data = result.revision.data();

			if (!this.checkRevision(data.im_revision_mobile))
			{
				return true;
			}
		}

		// recent block
		if (result.recent && !recentError)
		{
			console.info("RecentList.update: recent list", result.recent.data());

			this.list = this.convert.getListFormat(result.recent.data());
			this.redraw();
		}
		// counters block
		if (result.counters && !countersError)
		{
			let counters = result.counters.data();
			if (this.isRecent())
			{
				this.messageCount = counters['TYPE']['DIALOG']+counters['TYPE']['CHAT'];
				this.messageCountArray = {};
				for (let i in counters["DIALOG"])
				{
					if (counters["DIALOG"].hasOwnProperty(i))
					{
						this.messageCountArray[i] = counters["DIALOG"][i];
					}
				}
				for (let i in counters["CHAT"])
				{
					if (counters["CHAT"].hasOwnProperty(i))
					{
						this.messageCountArray['chat'+i] = counters["CHAT"][i];
					}
				}

				if (!this.isOpenlinesOperator())
				{
					this.messageCount += counters['TYPE']['LINES'];
					for (let i in counters["LINES"])
					{
						if (counters["LINES"].hasOwnProperty(i))
						{
							this.messageCountArray['chat'+i] = counters["LINES"][i];
						}
					}
				}
			}
			else
			{
				this.messageCount = counters['TYPE']['LINES'];
				this.messageCountArray = {};
				for (let i in counters["LINES"])
				{
					if (counters["LINES"].hasOwnProperty(i))
					{
						this.messageCountArray['chat'+i] = counters["LINES"][i];
					}
				}
			}

			this.notify.counter = counters['TYPE']['NOTIFY'];

			this.notify.refresh();
			this.updateCounter(false);
		}

		// userData block
		if (result.userData && !userDataError)
		{
			this.userData = this.convert.getUserDataFormat(result.userData.data());
		}

		// last search block
		if (this.isRecent() && result.lastSearch && !lastSearchError)
		{
			console.info("RecentList.refresh: update last search", result.lastSearch.data());

			this.search.recent = this.convert.getListFormat(result.lastSearch.data());
		}

		// colleagues list block
		if (this.isRecent() && result.colleagues && !colleaguesError)
		{
			console.info("RecentList.refresh: update colleagues list", result.colleagues.data());

			this.colleaguesList = this.convert.getUserListFormat(result.colleagues.data());
		}

		if (!recentError || !userDataError || !lastSearchError || !colleaguesError)
		{
			this.cache.update({recent: true, colleagues: this.isRecent() && params.start, lastSearch: this.isRecent()});
		}

		// general actions for all modules
		// userCounters block
		if (this.isRecent() && result.userCounters && !userCountersError)
		{
			BX.postComponentEvent("onUpdateUserCounters", [result.userCounters.data()]);
			BX.postWebEvent("onUpdateUserCounters", result.userCounters.data(), true);
		}

		// serverTime block
		if (result.serverTime && !serverTimeError)
		{
			BX.postComponentEvent("onUpdateServerTime", [result.serverTime.data()], "communication");
		}

		console.info("RecentList.refresh: receive answer from server and update variables ("+(new Date() - executeTime)+'ms)', result);

		if (revisionError || recentError || countersError || userCountersError || userDataError)
		{
			let error = null;
			if (revisionError)
			{
				error = revisionError;
			}
			else if (recentError)
			{
				error = recentError;
			}
			else if (countersError)
			{
				error = countersError;
			}
			else if (userCountersError)
			{
				error = userCountersError;
			}
			else if (userDataError)
			{
				error = userDataError;
			}

			if (error)
			{
				if (error.ex.error == 'ERROR_NETWORK')
				{
					console.error("RecentList.refresh: connection error, stop trying connect", error.ex);
				}
				else if (error.ex.error == 'REQUEST_CANCELED')
				{
					console.error("RecentList.refresh: execute request canceled by user", error.ex);
				}
				else
				{
					console.error("RecentList.refresh: we have some problems with request, we will be check again soon\n", error.ex);

					clearTimeout(this.refreshTimeout);
					this.refreshTimeout = setTimeout(() => {
						this.refresh();
					}, this.listRequestAfterErrorInterval);
				}
			}
			dialogList.stopRefreshing();
		}
	}, false, (xhr) => {
		this.requestRegister('refresh', xhr);
	});

	return true;
};

RecentList.redraw = function()
{
	this.queue.clear();

	let listConverted = [];
	this.list.forEach((element) => {
		listConverted.push(this.convert.getElementFormat(element));
		this.messageCountArray[element.id] = element.counter;
	});

	if (listConverted.length <= 0)
	{
		listConverted = this.getEmptyElement();
		this.listEmpty = true;
	}

	dialogList.setItems(listConverted);

	dialogList.stopRefreshing();

	return true;
};

RecentList.getEmptyElement = function()
{
	let list = [];
	if (this.isRecent())
	{
		list.push({
			title : BX.message('IM_LIST_EMPTY'),
			type : "button",
			sectionCode: 'general',
			params: { id: "empty", type: 'openSearch'},
			unselectable : typeof dialogList.showSearchBar == 'undefined'
		});
	}
	else
	{
		list.push({
			title : BX.message('OL_LIST_EMPTY'),
			type : "button",
			sectionCode: 'general',
			params: { id: "empty", type: 'openSearch'},
			unselectable : true
		});
	}

	return list;
};

RecentList.getElement = function(elementId, clone)
{
	let index = this.list.findIndex((listElement) => listElement && listElement.id == elementId);
	if (index == -1)
	{
		return false;
	}

	return clone === true? Utils.objectClone(this.list[index]): this.list[index];
};

RecentList.getElementByMessageId = function(messageId, clone)
{
	let index = this.list.findIndex((listElement) => listElement && listElement.message.id == messageId);
	if (index == -1)
	{
		return false;
	}

	return clone === true? Utils.objectClone(this.list[index]): this.list[index];
};

RecentList.setElement = function(elementId, data, immediately)
{
	if (!data)
	{
		return false;
	}

	immediately = immediately === true;

	this.unblockElement(elementId);

	let index = this.list.findIndex((listElement) => listElement && listElement.id == elementId);
	if (index == -1)
	{
		elementId = data.id;

		this.list.push(data);
		this.messageCountArray[elementId] = data.counter;

		this.queue.add(this.queue.TYPE_ADD, elementId, data);
	}
	else
	{
		elementId = this.list[index].id;
		this.list[index] = data;
		this.messageCountArray[elementId] = data.counter;

		this.queue.add(this.queue.TYPE_UPDATE, elementId, data);
	}

	if (immediately)
	{
		this.queue.worker();
	}

	return true;
};

RecentList.updateElement = function(elementId, data, immediately)
{
	if (!data)
	{
		return false;
	}

	let index = this.list.findIndex((listElement) => listElement && listElement.id == elementId);
	if (index == -1)
	{
		return false;
	}

	immediately = immediately === true;

	this.unblockElement(elementId);

	if (!Utils.isObjectChanged(this.list[index], data))
	{
		return true;
	}

	elementId = this.list[index].id;

	this.list[index] = Utils.objectMerge(this.list[index], data);
	this.messageCountArray[elementId] = this.list[index].counter;

	this.queue.add(this.queue.TYPE_UPDATE, elementId, this.list[index]);

	if (immediately)
	{
		this.queue.worker();
	}

	return true;
};

RecentList.deleteElement = function(elementId)
{
	let index = this.list.findIndex((listElement) => listElement && listElement.id == elementId);
	if (index == -1)
	{
		return false;
	}

	this.unblockElement(elementId);

	elementId = this.list[index].id;

	delete this.list[index];
	delete this.messageCountArray[elementId];
	delete this.timer.list[elementId];

	this.updateCounter(false);
	this.cache.update({recent: true});

	this.queue.delete(this.queue.TYPE_ALL, elementId);
	dialogList.removeItem({"params.id" : elementId});

	this.listEmpty = true;
	for (let element of this.list)
	{
		if (typeof element != 'undefined')
		{
			this.listEmpty = false;
			break;
		}
	}
	if (this.listEmpty)
	{
		dialogList.setItems(this.getEmptyElement());
	}

	return true;
};

RecentList.blockElement = function(elementId, action, autoUnblockCallback, autoUnblockCallbackParams)
{
	this.blocked[elementId] = true;

	autoUnblockCallbackParams = typeof autoUnblockCallbackParams == 'undefined'? {}: autoUnblockCallbackParams;
	autoUnblockCallbackParams.__callback = typeof autoUnblockCallback == 'function'? autoUnblockCallback: () => {};

	this.timer.start('block', elementId, 30000, (id, params) => {
		this.unblockElement(id, false);
		params.__callback(id, params);
	}, autoUnblockCallbackParams);

	return true;
};

RecentList.unblockElement = function(elementId, runCallback)
{
	delete this.blocked[elementId];

	let skipCallback = runCallback !== true;
	this.timer.stop('block', elementId, skipCallback);

	return true;
};

RecentList.isElementBlocked = function (elementId)
{
	return this.blocked[elementId] === true;
};

RecentList.requestRegister = function(name, xhr)
{
	this.request[name] = xhr;
	return true;
};

RecentList.requestUnregister = function (name, abort)
{
	if (this.request[name])
	{
		if (abort)
		{
			this.request[name].abort();
		}
		delete this.request[name];
	}
};

RecentList.requestGet = function(name)
{
	return this.request[name]? this.request[name]: null;
};

RecentList.requestAbort = function(name)
{
	if (this.request[name])
	{
		this.request[name].abort();
	}
	return true;
};


RecentList.capturePullEvent = function (status)
{
	if (typeof(status) == 'undefined')
	{
		status = !this.debugLog;
	}

	console.info('RecentList.capturePullEvent: capture "Pull Event" '+(status? 'enabled': 'disabled'));
	this.debugLog = !!status;

	BX.componentParameters.set('PULL_DEBUG', this.debugLog)
};



/* Cache API */
RecentList.cache = {
	updateTimeout: '',
	updateInterval: 2000,
	database: {},
};

RecentList.cache.init = function ()
{
	let executeTimeRecent = new Date();
	this.database.table(tables.recent).then(table =>
	{
		table.get().then(items =>
		{
			if (items.length > 0)
			{
				let cacheData = JSON.parse(items[0].VALUE);

				if (typeof(cacheData.list) == 'undefined')
				{
					console.info("RecentList.cache.init: cache file \"recent\" has been ignored because it's old");
					return false;
				}

				if (this.base.list.length > 0)
				{
					this.push.setPullMessageHistory();
					console.info("RecentList.cache.init: cache file \"recent\" has been ignored because it was loaded a very late");
					return false
				}

				this.base.list = this.convert.getListFormat(cacheData.list);
				this.push.updateList(false);
				this.base.redraw();

				console.info("RecentList.cache.init: list items load from cache \"recent\" ("+(new Date() - executeTimeRecent)+'ms)', "count: "+this.base.list.length);

				if (cacheData.userData)
				{
					this.base.userData = cacheData.userData;
				}
			}
		})
	});

	let executeTimeLastSearch = new Date();
	this.database.table(tables.lastSearch).then(table =>
	{
		table.get().then(items =>
		{
			if (items.length > 0)
			{
				let cacheData = JSON.parse(items[0].VALUE);

				if (this.search.recent.length > 0)
				{
					console.info("RecentList.cache.init: cache file \"last search\" has been ignored because it was loaded a very late");
					return false
				}

				this.search.recent = this.convert.getListFormat(cacheData.recent);

				console.info("RecentList.cache.init: list items load from cache \"last search\" ("+(new Date() - executeTimeLastSearch)+'ms)', "count: "+this.search.recent.length);
			}
		})
	});

	let executeTimeColleaguesList = new Date();
	this.database.table(tables.colleaguesList).then(table =>
	{
		table.get().then(items =>
		{
			if (items.length > 0)
			{
				let cacheData = JSON.parse(items[0].VALUE);

				if (this.base.colleaguesList.length > 0)
				{
					console.info("RecentList.cache.init: cache file \"colleagues list\" has been ignored because it was loaded a very late");
					return false
				}

				this.base.colleaguesList = this.convert.getUserListFormat(cacheData.colleaguesList);

				console.info("RecentList.cache.init: list items load from cache \"colleagues list\" ("+(new Date() - executeTimeColleaguesList)+'ms)', "count: "+this.base.colleaguesList.length);
			}
		})
	});

	return true;
};

RecentList.cache.update = function (params)
{
	params = params || {recent: true, lastSearch: true, colleagues: true};

	clearTimeout(this.refreshTimeout);
	this.refreshTimeout = setTimeout(() =>
	{
		let executeTimeRecent = new Date();
		let executeTimeLastSearch = new Date();

		if (params.recent)
		{
			this.database.table(tables.recent).then(table =>
			{
				table.delete().then(() =>
				{
					table.add({value : {
						list: this.base.list,
						userData: this.base.userData
					}}).then(() =>
					{
						console.info("RecentList.cache.update: recent list items updated ("+(new Date() - executeTimeRecent)+'ms)', "count: "+this.base.list.length);
					});
				})
			});
		}

		if (params.colleagues)
		{
			this.database.table(tables.colleaguesList).then(table =>
			{
				table.delete().then(() =>
				{
					table.add({value : {colleaguesList: this.base.colleaguesList}}).then(() =>
					{
						console.info("RecentList.cache.update: colleagues list items updated ("+(new Date() - executeTimeLastSearch)+'ms)', "count: "+this.base.colleaguesList.length);
					});
				})
			});
		}

		if (params.lastSearch)
		{
			this.database.table(tables.lastSearch).then(table =>
			{
				table.delete().then(() =>
				{
					table.add({value : {recent: this.search.recent}}).then(() =>
					{
						console.info("RecentList.cache.update: last search items updated ("+(new Date() - executeTimeLastSearch)+'ms)', "count: "+this.search.recent.length);
					});
				})
			});
		}
	}, this.updateInterval);

	return true;
};



/* Convert API */
RecentList.convert = {};

RecentList.convert.getElementFormat = function(element)
{
	let item = {};

	item.id = element.id;

	item.params = {
		id : element.id,
		date : Utils.getTimestamp(element.message.date),
		type : element.type,
	};

	item.sortValues = {
		order : item.params.date
	};

	if (element.type == 'user')
	{
		item.title = element.user.name+(element.user.id == this.base.userId? ' ('+BX.message("IM_YOU")+')': '');
		item.imageUrl = Utils.getAvatar(element.user.avatar);
		item.color = element.user.color;
		item.sectionCode = element.pinned? 'pinned': 'general';
		item.subtitle = element.message.text;
	}
	else
	{
		item.title = element.chat.name;
		item.imageUrl = Utils.getAvatar(element.chat.avatar);

		if (element.chat.id == this.base.generalChatId && !item.imageUrl)
		{
			item.imageUrl = this.base.imagePath+'/avatar_general.png';
		}

		item.color = element.chat.color;
		if (this.base.isOpenlinesRecent() && element.chat.type == 'lines')
		{
			if (element.lines.status < 40)
			{
				item.sectionCode = 'work';
				let session = MessengerCommon.linesGetSession(element.chat);
				if (session && session.dateCreate)
				{
					item.sortValues.order = session.dateCreate;
					item.params.date = session.dateCreate;
				}
			}
			else
			{
				item.sectionCode = 'answered';
			}

			if (element.pinned)
			{
				item.sectionCode = 'pinned';
			}
		}
		else
		{
			item.sectionCode = element.pinned? 'pinned': 'general';
		}

		let prefix = '';
		if (element.message.author_id == this.base.userId)
		{
			prefix = BX.message('IM_YOU_2');
		}
		else if (element.message.author_id)
		{
			if (!element.user.first_name)
			{
				prefix = element.user.name+': ';
			}
			else
			{
				prefix = element.user.first_name+(element.user.last_name? ' '+element.user.last_name.substr(0, 1)+'.': '')+': ';
			}
		}

		item.subtitle = prefix+element.message.text;
	}

	item.messageCount = element.counter;

	item.backgroundColor = element.pinned? '#f6f6f6': '#ffffff';

	item.styles = {};
	item.styles.avatar = this.getAvatarFormat(element);
	item.styles.title = this.getTitleFormat(element);
	item.styles.subtitle = this.getTextFormat(element);
	item.styles.date = this.getDateFormat(element);
	item.styles.counter = this.getCounterFormat(element);

	item.actions = this.getActionList(element);

	this.updateRuntimeData(element);

	return item;
};

RecentList.convert.getElementFormatByEntity = function(type, entity)
{
	let result = {
		id: entity.id,
		type: type,
		message: {
			id: 0,
			text: "",
			file: false,
			author_id: 0,
			attach: false,
			date: new Date(),
			status: "received"
		},
	};

	if (type == 'user')
	{
		result.user = entity;
	}
	else
	{
		result.user = {};
		result.chat = entity;
	}

	return this.getElementFormat(result);
};

RecentList.convert.updateRuntimeData = function(element)
{
	let status = this.getUserImageCode(element);

	let updateRuntime = typeof element.runtime == 'undefined' || element.runtime.status != status;
	if (!updateRuntime)
	{
		return true;
	}

	let index = this.base.list.findIndex((listElement) => listElement && listElement.id == element.id);
	if (index == -1)
	{
		return false;
	}

	if (typeof element.runtime == 'undefined')
	{
		element.runtime = {};
		this.base.list[index].runtime = {};
	}

	if (element.runtime.status != status)
	{
		element.runtime.status = status;
		this.base.list[index].runtime.status = status;
	}

	return true;
};

RecentList.convert.getAvatarFormat = function(element)
{
	let result = {};
	if (element.type == 'user')
	{
		let status = this.getUserImageCode(element);
		if (status)
		{
			result = {image: {name: 'status_'+status}};
		}
	}
	else
	{
		if (element.chat.type == 'lines')
		{
			let status = this.getLinesImageCode(element);
			result = {image: {name: 'status_'+status}};

			let session = MessengerCommon.linesGetSession(element.chat);
			if (session.crm == 'Y')
			{
				result.additionalImage = {name: 'special_status_crm'};
			}
		}
		else
		{
			if (element.chat.id == this.base.generalChatId)
			{
				if (Utils.getAvatar(element.chat.avatar))
				{
					result = {
						image: {name: 'status_dialog_general'}
					};
				}
			}
			else if (element.chat.type == 'chat')
			{
				result = {
					image: {name: 'status_dialog_chat'}
				};
			}
			else if (element.chat.type == 'open')
			{
				result = {
					image: {name: 'status_dialog_open'}
				};
			}
		}
	}

	return result;
};

RecentList.convert.getTitleFormat = function(element)
{
	let result = {};
	if (element.type == 'user')
	{
		if (element.user.id == this.base.userId)
		{
			result = {
				image: {name: 'name_status_owner'}
			};
		}
		else if (element.user.network)
		{
			result = {
				color: '#0a962f',
				image: {name: 'name_status_network'}
			};
		}
		else if (element.user.bot)
		{
			result = {
				color: '#725acc',
				image: {name: 'name_status_bot'}
			};
		}
		else if (element.user.extranet)
		{
			result = {
				color: '#ca7b00',
				image: {name: 'name_status_extranet'}
			};
		}
		else
		{
			let status = MessengerCommon.getUserStatus(element.user);
			if (status == 'vacation')
			{
				result = {
					image: {name: 'name_status_vacation'}
				};
			}
			else if (status == 'birthday')
			{
				result = {
					image: {name: 'name_status_birthday'}
				};
			}
		}
	}
	else
	{
		if (element.chat.type == 'lines')
		{
			if (this.base.isRecent())
			{
				result = {
					color: '#16938b',
					image: {name: 'name_status_lines'},
				};
			}
			else if (element.chat.owner == this.base.userId)
			{
				result = {
					image: {name: 'name_status_owner'},
				};
			}
			else if (element.chat.owner == 0)
			{
				result = {
					image: {name: 'name_status_new'},
					color: '#e66467',
				};
			}
		}
		else if (element.chat.type == 'call')
		{
			result = {
				image: {name: 'name_status_call'},
			};
		}
		else
		{
			if (element.chat.extranet)
			{
				result = {
					color: '#ca7b00',
					image: {name: 'name_status_extranet'}
				};
			}
			if (element.chat.mute_list[this.base.userId])
			{
				result.additionalImage = {name: 'name_status_mute'};
			}
		}
	}

	return result;
};

RecentList.convert.getCounterFormat = function(element)
{
	let result = {};
	if (element.type != 'chat')
	{
		return result;
	}

	if (element.chat.type == 'lines' || element.chat.type == 'call')
	{
	}
	else
	{
		if (element.chat.mute_list[this.base.userId])
		{
			result = {backgroundColor: '#B8BBC1'};
		}
	}

	return result;
};

RecentList.convert.getDateFormat = function(element)
{
	let name = '';
	let sizeMultiplier = 0.7;
	if (element.message.author_id == this.base.userId)
	{
		if (element.type == 'user' && element.user.id == this.base.userId)
		{
			name = 'message_delivered';
		}
		else if (element.message.status == 'received')
		{
			name = 'message_send';
		}
		else if (element.message.status == 'error')
		{
			name = 'message_error';
		}
		else if (element.message.status == 'delivered')
		{
			name = 'message_delivered';
		}
		else if (element.pinned)
		{
			name = 'message_pin';
			sizeMultiplier = 0.9;
		}
	}
	else
	{
		if (element.pinned)
		{
			name = 'message_pin';
			sizeMultiplier = 0.9;
		}
		else
		{
			return {};
		}
	}

	return {image: {name: name, sizeMultiplier: sizeMultiplier}};
};

RecentList.convert.getTextFormat = function(element)
{
	let result = {};
	if (element.writing)
	{
		result = {animation:{color:"#777777", type:"bubbles"}};
	}
	else if (element.message.author_id == this.base.userId)
	{
		result = {image: {name : 'reply', sizeMultiplier: 0.7}};
	}

	return result;
};

RecentList.convert.getUserImageCode = function(element)
{
	let icon = '';
	if (element.type != 'user')
	{
		return '';
	}

	let data = MessengerCommon.getUserStatus(element.user, false);
	if (data.status == 'vacation' && (element.user.extranet || element.user.bot || element.user.network))
	{
		icon = data.status;
	}
	else if (data.status == 'birthday' && (element.user.extranet || element.user.bot || element.user.network))
	{
		icon = data.status;
	}
	else if (
		data.originStatus == 'away'
		|| data.originStatus == 'dnd'
		|| data.originStatus == 'guest'
		|| data.originStatus == 'idle'
		|| data.originStatus == 'mobile'
		|| data.originStatus == 'call'
	)
	{
		icon = data.originStatus;
	}

	return icon;
};

RecentList.convert.getLinesImageCode = function(element)
{
	if (element.type != 'chat' || element.chat.type != 'lines')
	{
		return '';
	}

	let result = 'world';
	let source = (element.chat.entity_id.split('|'))[0];

	if (source == 'livechat')
	{
		result = 'livechat';
	}
	else if (source == 'viber')
	{
		result = 'viber';
	}
	else if (source == 'telegrambot')
	{
		result = 'telegram';
	}
	else if (source == 'instagram')
	{
		result = 'instagram';
	}
	else if (source == 'vkgroup')
	{
		result = 'vk';
	}
	else if (source == 'facebook')
	{
		result = 'fbm';
	}
	else if (source == 'facebookcomments')
	{
		result = 'facebook';
	}
	else if (source == 'network')
	{
		result = 'network';
	}
	else if (source == 'botframework.skype')
	{
		result = 'skype';
	}
	else if (source == 'botframework.slack')
	{
		result = 'slack';
	}
	else if (source == 'botframework.kik')
	{
		result = 'kik';
	}
	else if (source == 'botframework.groupme')
	{
		result = 'groupme';
	}
	else if (source == 'botframework.twilio')
	{
		result = 'twilio';
	}
	else if (source == 'botframework.webchat')
	{
		result = 'webchat';
	}
	else if (source == 'botframework.emailoffice365')
	{
		result = 'email';
	}
	else if (source == 'botframework.telegram')
	{
		result = 'telegram';
	}
	else if (source == 'botframework.facebookmessenger')
	{
		result = 'fbm';
	}

	return result;
};

RecentList.convert.getActionList = function(element)
{
	let result = false;
	if (element.type == 'user')
	{
		result = [];
		result.push({
			title : element.pinned? BX.message("ELEMENT_MENU_UNPIN"): BX.message("ELEMENT_MENU_PIN"),
			identifier : element.pinned? "unpin": "pin",
			color : "#3e99ce",
			iconName : "action_"+(element.pinned? "unpin": "pin"),
		});
		result.push({
			title : BX.message("ELEMENT_MENU_DELETE"),
			identifier : "hide",
			iconName : "action_delete",
			color : "#df532d",
		});
	}
	else
	{
		if (element.chat.type == 'lines')
		{
			if (element.chat.owner == 0)
			{
				result = [
					{
						title : BX.message("ELEMENT_MENU_ANSWER"),
						identifier : "operatorAnswer",
						iconName : "action_answer",
						color : "#aac337"
					},
					{
						title : BX.message("ELEMENT_MENU_SKIP"),
						color : "#df532d",
						iconName : "action_skip",
						identifier : "operatorSkip",
					},
					{
						title : BX.message("ELEMENT_MENU_SPAM"),
						color : "#e89d2a",
						iconName : "action_spam",
						identifier : "operatorSpam",
					},
				];
			}
			else if (element.chat.owner == this.base.userId)
			{
				result = [
					{
						title : BX.message("ELEMENT_MENU_FINISH"),
						iconName : "action_finish",
						identifier : "operatorFinish",
						color : "#aac337",
					},
					{
						title : element.pinned? BX.message("ELEMENT_MENU_UNPIN"): BX.message("ELEMENT_MENU_PIN"),
						identifier : element.pinned? "unpin": "pin",
						iconName : "action_"+(element.pinned? "unpin": "pin"),
						color : "#3e99ce"
					},
					{
						title : BX.message("ELEMENT_MENU_SPAM"),
						color : "#e8a441",
						identifier : "operatorSpam",
						iconName : "action_spam",
					},
				];
			}
			else
			{
				result = [
					{
						title : element.pinned? BX.message("ELEMENT_MENU_UNPIN"): BX.message("ELEMENT_MENU_PIN"),
						identifier : element.pinned? "unpin": "pin",
						iconName : "action_"+(element.pinned? "unpin": "pin"),
						color : "#3e99ce"
					},
					{
						title : BX.message("ELEMENT_MENU_LEAVE"),
						identifier : "leave",
						iconName : "action_delete",
						color : "#df532d",
					},
				];
			}
		}
		else
		{
			result = [
				{
					title : element.chat.mute_list[this.base.userId]? BX.message("ELEMENT_MENU_UNMUTE"): BX.message("ELEMENT_MENU_MUTE"),
					identifier : element.chat.mute_list[this.base.userId]? "unmute": "mute",
					iconName : "action_"+(element.chat.mute_list[this.base.userId]? "unmute": "mute"),
					color : "#aaabac"
				},
				{
					title : element.pinned? BX.message("ELEMENT_MENU_UNPIN"): BX.message("ELEMENT_MENU_PIN"),
					iconName : "action_"+(element.pinned? "unpin": "pin"),
					identifier : element.pinned? "unpin": "pin",
					color : "#3e99ce"
				},
				{
					title : BX.message("ELEMENT_MENU_DELETE"),
					iconName : "action_delete",
					identifier : "hide",
					color : "#df532d"
				},
			];
		}
	}

	return result;
};

RecentList.convert.getListFormat = function (list)
{
	let result = [];

	list.forEach((element) => {
		if (!element) return;

		element.user = this.getUserDataFormat(element.user);

		if (typeof element.message != 'undefined')
		{
			element.message.date = new Date(element.message.date);
		}

		if (typeof element.chat != 'undefined')
		{
			element.chat.date_create = new Date(element.chat.date_create);
		}

		result.push(element);
	});

	return result;
};

RecentList.convert.getUserListFormat = function (list)
{
	let result = [];

	list.forEach((element) => {
		if (!element) return;

		element = this.getUserDataFormat(element);

		result.push(element);
	});

	return result;
};

RecentList.convert.getUserDataFormat = function (user)
{
	if (!user)
	{
		user = {id: 0};
	}
	if (user.id > 0)
	{
		if (typeof (user.last_activity_date) != 'undefined')
		{
			user.last_activity_date = new Date(user.last_activity_date);
		}
		if (typeof (user.mobile_last_date) != 'undefined')
		{
			user.mobile_last_date = new Date(user.mobile_last_date);
		}
		if (typeof (user.idle) != 'undefined')
		{
			user.idle = user.idle? new Date(user.idle): false;
		}
		if (typeof (user.absent) != 'undefined')
		{
			user.absent = user.absent? new Date(user.absent): false;
		}
	}

	return user;
};

RecentList.convert.getSearchElementFormat = function(element, recent)
{
	let item = {};
	let type = '';

	if (recent)
	{
		type = element.type;

		item = Utils.objectClone(element);
		item.sectionCode = 'recent';

		item.actions = [{
			title : BX.message("ELEMENT_MENU_DELETE"),
			identifier : "delete",
			destruct: true,
			color : "#df532d"
		}];
	}
	else
	{
		type = typeof element.owner == 'undefined'? 'user': 'chat';
		let elementClone = Utils.objectClone(element);

		element = {type: type};
		element[type] = elementClone;

		item.sectionCode = type;
	}

	item.params = {
		action: 'item'
	};

	if (type == 'user')
	{
		item.params.id = element.user.id;

		item.title = element.user.name+(element.user.id == this.base.userId? ' ('+BX.message("IM_YOU")+')': '');
		item.imageUrl = Utils.getAvatar(element.user.avatar);
		item.color = element.user.color;

		item.subtitle = element.user.work_position? element.user.work_position: BX.message("IM_LIST_EMPLOYEE");
	}
	else
	{
		item.params.id = 'chat'+element.chat.id;

		item.title = element.chat.name;
		item.imageUrl = Utils.getAvatar(element.chat.avatar);
		item.color = element.chat.color;
	}

	item.styles = {};
	item.styles.title = this.getTitleFormat(element);

	return item;
};


RecentList.convert.getPushFormat = function(push)
{
	if (typeof (push) !== 'object' || typeof (push.params) === 'undefined')
	{
		return {'ACTION' : 'NONE'};
	}

	let result = {};
	try
	{
		result = JSON.parse(push.params);
	}
	catch (e)
	{
		result = {'ACTION' : push.params};
	}

	return result;
};


/* Push & Pull API */
RecentList.push = {
	history: {},
};
RecentList.push.init = function()
{
	if (this.base.isRecent())
	{
		this.manager = Application.getNotificationHistory("im_message");
	}
	else
	{
		this.manager = Application.getNotificationHistory("im_lines_message");
	}

	this.manager.setOnChangeListener(() => {
		BX.onViewLoaded(() =>
		{
			this.updateList();
		});
	});
};

RecentList.push.getOpenDialogParams = function(dialogId)
{
	let chatData = null;
	let userData = null;
	let messageHistory = null;

	let element = this.base.getElement(dialogId, true);
	if (element)
	{
		if (element.type == 'user')
		{
			userData = JSON.stringify(element.user);
		}
		else if (element.type == 'chat')
		{
			chatData =  JSON.stringify(element.chat);
			userData = JSON.stringify(element.user);
		}
	}

	if (typeof(this.history[dialogId]) != 'undefined')
	{
		messageHistory = JSON.stringify(this.history[dialogId]);
		delete this.history[dialogId];
	}

	return {
		dialogId : dialogId,
		type : element.type,
		chat : chatData,
		user : userData,
		messageHistory : messageHistory,
	};
};

RecentList.push.setPullMessageHistory = function()
{
	for (let dialogId in list)
	{
		if (!list.hasOwnProperty(dialogId) || list[dialogId].length <= 0)
		{
			continue;
		}

		list[dialogId].map((push) => {
			let element = this.getFormatHistoryElement(push);
			if (element)
			{
				if (typeof this.history[push.id] == 'undefined')
				{
					this.history[push.id] = {};
				}
				this.history[push.id][push.messageId] = element;
			}
		});
	}
};

RecentList.push.updateList = function(draw)
{
	draw = draw !== false;

	let list = this.manager.get();
	if (!list)
		return true;

	console.info('RecentList.push.updateList: parse push messages', list);

	for (let dialogId in list)
	{
		if (!list.hasOwnProperty(dialogId) || list[dialogId].length <= 0)
		{
			continue;
		}

		this.updateElement(list[dialogId][list[dialogId].length-1]);

		list[dialogId].map((push) => {
			let element = this.getFormatHistoryElement(push);
			if (element)
			{
				if (typeof this.history[push.id] == 'undefined')
				{
					this.history[push.id] = {};
				}
				this.history[push.id][push.messageId] = element;
			}
		});
	}

	this.manager.clear();

	if (draw)
	{
		this.queue.worker()
	}

	return true;
};

RecentList.push.getFormatHistoryElement = function(push)
{
	let element = false;
	if (push.type == 'user')
	{
		if (!this.base.isRecent())
		{
			return false;
		}

		element = {
			isChat: false,
			chatId: 0,
			params: {},
			id: push.messageId,
			text: push.messageText,
			recipientId: push.id,
			senderId: push.messageAuthorId,
			date: new Date(push.messageDate),
		};
	}
	else if (push.type == 'chat')
	{
		if (push.chatType == "lines")
		{
			if (this.base.isRecent() && this.base.isOpenlinesOperator())
			{
				return false;
			}
		}
		else if (!this.base.isRecent())
		{
			return false;
		}

		element = {
			isChat: true,
			chatId: parseInt(push.id.substr(4)),
			id: push.messageId,
			text: push.messageText,
			recipientId: push.id,
			senderId: push.messageAuthorId,
			date: new Date(push.messageDate),
		};
	}

	return element;
};

RecentList.push.updateElement = function(push)
{
	if (push.type == 'user')
	{
		if (!this.base.isRecent())
		{
			return false;
		}

		let index = this.base.list.findIndex((listElement) => listElement && listElement.id == push.id);
		if (index == -1)
		{
			let newElement = {
				id: push.id,
				user: {
					id: push.messageAuthorId,
					avatar: push.avatarUrl,
					color: push.userColor,
					name: push.userName,
					first_name: push.userFirstName,
					last_name: push.userLastName,
					extranet: push.userExtranet,
				},
				message: {
					id: push.messageId,
					text: push.messageText,
					author_id: push.messageAuthorId,
					date: push.messageDate,
				},
				counter: push.counter,
			};
			this.base.setElement(push.id, this.getFormattedElement(newElement));
		}
		else
		{
			if (this.base.list[index].message.id >= push.messageId)
				return false;

			this.base.updateElement(push.id, {
				counter: push.counter,
				message: {
					author_id: push.messageAuthorId,
					text: push.messageText,
					date: new Date(push.messageDate)
				}
			});
		}
	}
	else if (push.type == 'chat')
	{
		let index = this.base.list.findIndex((listElement) => listElement && listElement.id == push.id);
		if (index == -1)
		{
			if (push.chatType == "lines")
			{
				if (this.base.isRecent() && this.base.isOpenlinesOperator())
				{
					return false;
				}
			}
			else if (!this.base.isRecent())
			{
				return false;
			}

			let newElement = {
				id: push.id,
				user: {
					id: push.messageAuthorId,
					avatar: push.avatarUrl,
					name: push.userName,
					first_name: push.userFirstName,
					last_name: push.userLastName,
					extranet: push.userExtranet,
				},
				message: {
					id: push.messageId,
					text: push.messageText,
					author_id: push.messageAuthorId,
					date: push.messageDate,
				},
				chat: {
					id: parseInt(push.id.substr(4)),
					avatar: push.chatUrl,
					color: push.chatColor,
					extranet: push.chatExtranet,
					name: push.chatName,
					type: push.chatType,
					entity_id: push.chatEntityId,
					entity_data_1: push.chatEntityData1,
					mute_list: [],
				},
				counter: push.counter,
			};
			if (push.chatType == 'lines')
			{
				newElement['lines'] = {
					id: push.linesId,
					status: push.linesStatus
				};
			}
			this.base.setElement(push.id, this.getFormattedElement(newElement));
		}
		else
		{
			if (this.base.list[index].message.id >= push.messageId)
				return false;

			let updateElement = {
				counter: push.counter,
				message: {
					author_id: push.messageAuthorId,
					text: push.messageText,
					date: new Date(push.messageDate)
				}
			};
			if (push.chatType == 'lines')
			{
				updateElement['lines'] = {
					id: push.linesId,
					status: push.linesStatus
				};
			}

			this.base.updateElement(push.id, updateElement);
		}
	}

	console.info('RecentList.push.updateElement: ', push);

	return true;
};

RecentList.push.getFormattedElement = function(element)
{
	let newElement = {
		avatar: {},
		user: {id: 0},
		message: {},
		counter: 0,
		blocked: false,
		writing: false,
	};

	if (element.id.toString().indexOf('chat') == 0)
	{
		newElement.type = 'chat';
		newElement.id = element.id;
		newElement.chat = {};
		if (typeof element.chat == 'undefined')
		{
			return false;
		}
	}
	else
	{
		newElement.type = 'user';
		newElement.id = parseInt(element.id);
		newElement.user = {};
		if (typeof element.user == 'undefined')
		{
			return false;
		}
	}

	newElement.message.id = parseInt(element.message.id);
	newElement.message.text = element.message.text;
	newElement.message.author_id = element.message.author_id && element.message.system != 'Y'? element.message.author_id: 0;
	newElement.message.date = new Date(element.message.date);
	newElement.message.file = element.message.params && element.message.params.FILE_ID? element.message.params.FILE_ID.length > 0: false;
	newElement.message.attach = element.message.params && element.message.params.ATTACH? element.message.params.ATTACH.length > 0: false;
	newElement.message.status = element.message.status? element.message.status: '';

	if (typeof element.counter != 'undefined')
	{
		newElement.counter = element.counter;
	}
	if (typeof element.writing != 'undefined')
	{
		newElement.writing = element.writing;
	}

	if (typeof element.user != 'undefined')
	{
		element.user.id = parseInt(element.user.id);
		if (element.user.id > 0)
		{
			newElement.user = element.user = this.convert.getUserDataFormat(element.user);

			if (newElement.type == 'user')
			{
				newElement.avatar.url = element.user.avatar;
				newElement.avatar.color = element.user.color;
				newElement.title = element.user.name;
			}
		}
		else
		{
			newElement.user = element.user;
		}
	}

	if (newElement.type == 'chat' && typeof element.chat != 'undefined')
	{
		element.chat.id = parseInt(element.chat.id);
		element.chat.date_create = new Date(element.chat.date_create);
		newElement.chat = element.chat;

		newElement.avatar.url = element.chat.avatar;
		newElement.avatar.color = element.chat.color;
		newElement.title = element.chat.name;

		if (element.chat.type == 'lines' && element.lines != 'undefined')
		{
			if (typeof newElement.lines == 'undefined')
			{
				newElement.lines = {};
			}
			newElement.lines.id = parseInt(element.lines.id);
			newElement.lines.status = parseInt(element.lines.status);
		}
	}

	return newElement;
};

RecentList.push.actionExecute = function()
{
	if (Application.isBackground())
		return false;

	let push = Application.getLastNotification();
	if (push === {})
	{
		return false;
	}

	console.info("RecentList.push.actionExecute: execute push-notification", push);
	let pushParams = this.convert.getPushFormat(push);
	if (pushParams.TAG)
	{
		pushParams.ACTION = pushParams.TAG;
	}

	if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) === 'IM_MESS_')
	{
		if (this.base.isOpenlinesRecent())
		{
			return false;
		}

		let user = parseInt(pushParams.ACTION.substr(8));
		if (user > 0)
		{
			this.base.openDialog(user, true);
		}
	}
	else if (pushParams.ACTION && pushParams.ACTION.substr(0, 8) === 'IM_CHAT_')
	{
		if (this.base.isRecent())
		{
			if (this.base.isOpenlinesOperator() && pushParams.CHAT_TYPE == 'L')
			{
				return false;
			}
		}
		else
		{
			if (pushParams.CHAT_TYPE != 'L')
			{
				return false;
			}
		}

		let chatId = parseInt(pushParams.ACTION.substr(8));
		if (chatId > 0)
		{
			this.base.openDialog('chat' + chatId, true);
		}
	}

	return true;
};



RecentList.pull = {};

RecentList.pull.init = function ()
{
	BX.addCustomEvent("onPullEvent-im", this.eventExecute.bind(this));

	if (this.base.isRecent())
	{
		BX.addCustomEvent("onPullOnlineEvent", this.eventOnlineExecute.bind(this));
	}
	else
	{
		BX.addCustomEvent("onPullEvent-imopenlines", this.eventLinesExecute.bind(this));
	}
};

RecentList.pull.getUserDataFormat = function (user)
{
	user = this.convert.getUserDataFormat(user);

	if (user.id > 0)
	{
		if (typeof (user.name) != 'undefined')
		{
			user.name = Utils.htmlspecialcharsback(user.name);
		}
		if (typeof (user.last_name) != 'undefined')
		{
			user.last_name = Utils.htmlspecialcharsback(user.last_name);
		}
		if (typeof (user.first_name) != 'undefined')
		{
			user.first_name = Utils.htmlspecialcharsback(user.first_name);
		}
		if (typeof (user.work_position) != 'undefined')
		{
			user.work_position = Utils.htmlspecialcharsback(user.work_position);
		}
	}

	return user;
};

RecentList.pull.getFormattedElement = function(element)
{
	let newElement = {};
	let index = this.base.list.findIndex((listElement) => listElement && listElement.id == element.id);
	if (index > -1)
	{
		newElement = Utils.objectClone(this.base.list[index]);
	}
	else
	{
		newElement = {
			avatar: {},
			user: {id: 0},
			message: {},
			counter: 0,
			blocked: false,
			writing: false,
		};
		if (element.id.toString().indexOf('chat') == 0)
		{
			newElement.type = 'chat';
			newElement.id = element.id;
			newElement.chat = {};
			if (typeof element.chat == 'undefined')
			{
				return false;
			}
		}
		else
		{
			newElement.type = 'user';
			newElement.id = parseInt(element.id);
			newElement.user = {};
			if (typeof element.user == 'undefined')
			{
				return false;
			}
		}
		if (typeof element.message == 'undefined')
		{
			return false;
		}
	}

	if (typeof element.message != 'undefined')
	{
		newElement.message.id = parseInt(element.message.id);
		newElement.message.text = element.message.text;
		newElement.message.author_id = element.message.senderId && element.message.system != 'Y'? element.message.senderId: 0;
		newElement.message.date = new Date(element.message.date);
		newElement.message.file = element.message.params && element.message.params.FILE_ID? element.message.params.FILE_ID.length > 0: false;
		newElement.message.attach = element.message.params && element.message.params.ATTACH? element.message.params.ATTACH.length > 0: false;
		newElement.message.status = element.message.status? element.message.status: '';
	}

	if (typeof element.counter != 'undefined')
	{
		newElement.counter = element.counter;
	}
	if (typeof element.writing != 'undefined')
	{
		newElement.writing = element.writing;
	}

	if (typeof element.user != 'undefined')
	{
		element.user.id = parseInt(element.user.id);
		if (element.user.id > 0)
		{
			newElement.user = element.user = this.getUserDataFormat(element.user);

			if (newElement.type == 'user')
			{
				newElement.avatar.url = element.user.avatar;
				newElement.avatar.color = element.user.color;
				newElement.title = element.user.name;
			}
		}
		else
		{
			newElement.user = element.user;
		}
	}

	if (newElement.type == 'chat' && typeof element.chat != 'undefined')
	{
		element.chat.id = parseInt(element.chat.id);
		element.chat.date_create = new Date(element.chat.date_create);
		newElement.chat = element.chat;

		newElement.avatar.url = element.chat.avatar;
		newElement.avatar.color = element.chat.color;
		newElement.title = element.chat.name;

		if (element.chat.type == 'lines' && element.lines != 'undefined')
		{
			if (typeof newElement.lines == 'undefined')
			{
				newElement.lines = {};
			}
			newElement.lines.id = parseInt(element.lines.id);
			newElement.lines.status = parseInt(element.lines.status);
		}
	}

	return newElement;
};

RecentList.pull.eventExecute = function(command, params, extra)
{
	if (!this.base.checkRevision(extra.im_revision_mobile) || extra.server_time_ago > 30)
	{
		return true;
	}
	if (this.base.debugLog)
	{
		console.warn("RecentList.pull.eventExecute: receive \""+command+"\"", params);
	}

	if (command == 'message' || command == 'messageChat')
	{
		if (this.base.isRecent())
		{
			if (command == 'messageChat' && params.chat[params.chatId].type == 'lines' && this.base.isOpenlinesOperator())
			{
				return false;
			}
		}
		else
		{
			if (command == 'message')
			{
				return false;
			}
			else if (params.chat[params.chatId].type != 'lines')
			{
				return false;
			}
		}

		if (command == 'messageChat' && params.userInChat[params.chatId].indexOf(this.base.userId) == -1)
		{
			this.base.updateElement(params.userId, {
				user: { idle: false, last_activity_date: new Date()}
			});

			return false;
		}

		params.message.text = MessengerCommon.purifyText(params.message.text, params.message.params);

		params.message.status = params.message.senderId == this.base.userId? 'received': '';

		if (command == 'message')
		{
			let recipientId = params.message.senderId == this.base.userId? params.message.recipientId: params.message.senderId;

			let addToRecent = params.notify !== true && params.notify.indexOf(this.base.userId) == -1? this.base.getElement(recipientId): true;
			if (addToRecent)
			{
				this.base.setElement(recipientId, this.getFormattedElement({
					id: recipientId,
					user: params.users[recipientId],
					message: params.message,
					counter: params.counter
				}));
			}
			this.action.writing(recipientId, false);
		}
		else if (command == 'messageChat')
		{
			let addToRecent = params.notify !== true && params.notify.indexOf(this.base.userId) == -1? this.base.getElement(params.message.recipientId): true;
			if (addToRecent)
			{
				this.base.setElement(params.message.recipientId, this.getFormattedElement({
					id: params.message.recipientId,
					chat: params.chat[params.chatId],
					user: params.message.senderId > 0? params.users[params.message.senderId]: {id: 0},
					lines: params.lines[params.chatId],
					message: params.message,
					counter: params.counter
				}));
			}

			this.action.writing(params.message.recipientId, false);

			this.base.updateElement(params.userId, {
				user: { idle: false, last_activity_date: new Date()}
			});
		}
	}
	else if (
		command == 'readMessageOpponent' || command == 'readMessageChatOpponent' ||
		command == 'unreadMessageOpponent' || command == 'unreadMessageChatOpponent'
	)
	{
		if (
			this.base.isOpenlinesRecent()
			&& (command == 'readMessageOpponent' || command == 'unreadMessageOpponent')
		)
		{
			return false;
		}

		let element = this.base.getElement(params.dialogId);
		if (!element)
		{
			return false;
		}

		if (
			params.chatMessageStatus
			&& params.chatMessageStatus != element.message.status
		)
		{
			this.base.updateElement(params.dialogId, {
				message: { status: params.chatMessageStatus},
			});
		}

		this.base.updateElement(params.userId, {
			user: { idle: false, last_activity_date: new Date()}
		});
	}
	else if (
		command == 'readMessage' || command == 'readMessageChat' ||
		command == 'unreadMessage' || command == 'unreadMessageChat'
	)
	{
		if (
			this.base.isOpenlinesRecent()
			&& (command == 'readMessage' || command == 'unreadMessage')
		)
		{
			return false;
		}

		this.base.updateElement(params.dialogId, {
			counter: params.counter
		});
	}
	else if (command == 'startWriting')
	{
		if (
			this.base.isOpenlinesRecent()
			&& (params.dialogId.toString().substr(0,4) != 'chat')
		)
		{
			return false;
		}
		this.action.writing(params.dialogId, true);
	}
	else if (
		command == 'messageUpdate'
		|| command == 'messageDelete'
		|| command == 'messageDeleteComplete'
	)
	{
		let element = this.base.getElementByMessageId(params.id, true);
		if (!element)
		{
			return false;
		}

		element.message.text = MessengerCommon.purifyText(params.text, params.params);
		element.message.params = params.params;
		element.message.file = params.params && params.params.FILE_ID? params.params.FILE_ID.length > 0: false;
		element.message.attach = params.params && params.params.ATTACH? params.params.ATTACH.length > 0: false;

		this.base.updateElement(element.id, element);
		this.action.writing(element.id, false);
	}
	else if (command == 'chatRename')
	{
		this.base.updateElement('chat'+params.chatId, {
			title: params.name,
			chat: { name: params.name}
		});

		this.search.updateElement('chat'+params.chatId, {
			title: params.name,
			chat: { name: params.name}
		});
	}
	else if (command == 'chatAvatar')
	{
		this.base.updateElement('chat'+params.chatId, {
			avatar: {url: params.avatar},
			chat: {avatar: params.avatar}
		});

		this.search.updateElement('chat'+params.chatId, {
			avatar: {url: params.avatar},
			chat: {avatar: params.avatar}
		});
	}
	else if (command == 'chatChangeColor')
	{
		this.base.updateElement('chat'+params.chatId, {
			avatar: {color: params.color},
			chat: {color: params.color}
		});
		this.search.updateElement('chat'+params.chatId, {
			avatar: {color: params.color},
			chat: {color: params.color}
		});
	}
	else if (command == 'chatUpdate')
	{
		let params = {};
		if (params.name == 'name')
		{
			params.title = params.value;
			params.chat = {};
			params.chat.name = params.value;
		}
		else if (params.name == 'color')
		{
			params.avatar = {};
			params.avatar.color = params.value;
			params.chat = {};
			params.chat.color = params.value;
		}
		else if (params.name == 'avatar')
		{
			params.avatar = {};
			params.avatar.url = params.value;
			params.chat = {};
			params.chat.avatar = params.value;
		}
		else if (params.name == 'date_create')
		{
			params.chat = {};
			params.chat.date_create = new Date(params.value);
		}

		this.base.updateElement('chat'+params.chatId, params);
		this.search.updateElement('chat'+params.chatId, params);
	}
	else if (command == 'updateUser' || command == 'updateBot')
	{
		if (this.base.isOpenlinesRecent())
		{
			return false;
		}
		this.base.updateElement(params.user.id, this.getFormattedElement({
			id: params.user.id,
			user: params.user,
		}));
		this.search.updateElement(params.user.id, this.getFormattedElement({
			id: params.user.id,
			user: params.user,
		}));
	}
	else if (command == 'chatMuteNotify')
	{
		let muteList = {};
		muteList[this.base.userId] = params.mute;

		this.base.updateElement(params.dialogId, {
			chat: { mute_list: muteList }
		});
		this.search.updateElement(params.dialogId, {
			chat: { mute_list: muteList }
		});
	}
	else if (command == 'chatHide')
	{
		this.base.deleteElement(params.dialogId);
	}
	else if (command == 'chatPin')
	{
		this.base.updateElement(params.dialogId, {
			pinned: params.active,
		}, true);
	}
	else if (command == 'deleteBot')
	{
		if (this.base.isOpenlinesRecent())
		{
			return false;
		}
		this.base.deleteElement(params.botId);
		this.search.deleteElement(params.botId);
	}
	else if (command == 'chatUserLeave')
	{
		if (params.userId == this.base.userId)
		{
			this.base.deleteElement('chat'+params.chatId);
			this.search.deleteElement('chat'+params.chatId);
		}
	}
	else if (this.base.isRecent())
	{
		if (command == 'notify')
		{
			this.notify.counter = params.counter;
			this.notify.refresh();
			this.base.updateCounter(false);
		}
		else if (command == 'readNotifyList' || command == 'unreadNotifyList' || command == 'confirmNotify')
		{
			this.notify.counter = params.counter;
			this.base.updateCounter(false);
			if (command != 'readNotifyList')
			{
				this.notify.refresh();
			}
		}
	}
	else if (command == 'generalChatId')
	{
		this.base.generalChatId = params.id;
		BX.componentParameters.set('IM_GENERAL_CHAT_ID', params.id)
	}
};

RecentList.pull.eventLinesExecute = function(command, params, extra)
{
	if (extra.server_time_ago > 30)
	{
		return false;
	}
	if (this.base.debugLog)
	{
		console.warn("RecentList.pull.eventLinesExecute: receive \""+command+"\"", params);
	}

	if (command == 'updateSessionStatus')
	{
		this.base.updateElement('chat'+params.chatId, {
			lines: { status: params.status }
		});
	}
};

RecentList.pull.eventOnlineExecute = function(command, params, extra)
{
	if (extra.server_time_ago > 30)
	{
		return false;
	}
	if (this.base.debugLog)
	{
		console.warn("RecentList.pull.eventOnlineExecute: receive \""+command+"\"", params);
	}

	if (command == 'list' || command == 'userStatus')
	{
		for (let i in params.users)
		{
			if(params.users.hasOwnProperty(i))
			{
				this.base.updateElement(params.users[i].id, {
					user: this.getUserDataFormat(params.users[i])
				});
			}
		}
	}
};



/* Queue API */
RecentList.queue = {
	TYPE_ALL: 'all',
	TYPE_ADD: 'add',
	TYPE_UPDATE: 'update',
};

RecentList.queue.init = function()
{
	this.list = {};
	this.list[this.TYPE_ADD] = {};
	this.list[this.TYPE_UPDATE] = {};

	this.updateInterval = 1000;
	this.updateListInterval = 59000;

	clearInterval(this.updateIntervalId);
	this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval);

	clearInterval(this.updateListIntervalId);
	this.updateListIntervalId = setInterval(this.listWorker.bind(this), this.updateListInterval);
};

RecentList.queue.add = function(type, id, element)
{
	if (type == this.TYPE_ALL)
	{
		return false;
	}
	this.list[type][id] = element;
	return true;
};

RecentList.queue.delete = function(type, id)
{
	if (type == this.TYPE_ALL)
	{
		delete this.list[this.TYPE_ADD][id];
		delete this.list[this.TYPE_UPDATE][id];
	}
	else
	{
		delete this.list[type][id];
	}

	return true;
};

RecentList.queue.clear = function()
{
	this.list[this.TYPE_ADD] = {};
	this.list[this.TYPE_UPDATE] = {};

	return true;
};

RecentList.queue.worker = function()
{
	let executeTime = new Date();

	let listAdd = [];
	for (let id in this.list[this.TYPE_ADD])
	{
		if(!this.list[this.TYPE_ADD].hasOwnProperty(id))
		{
			continue;
		}
		listAdd.push(this.convert.getElementFormat(this.list[this.TYPE_ADD][id]));
		delete this.list[this.TYPE_ADD][id];
	}
	if (listAdd.length > 0)
	{
		if (this.base.listEmpty)
		{
			this.base.listEmpty = false;
			dialogList.removeItem({"params.id" : "empty"});
		}

		dialogList.addItems(listAdd);
	}

	let listUpdate = [];
	for (let id in this.list[this.TYPE_UPDATE])
	{
		if(!this.list[this.TYPE_UPDATE].hasOwnProperty(id))
		{
			continue;
		}
		listUpdate.push({
			filter: {"params.id" : this.list[this.TYPE_UPDATE][id]['id']},
			element: this.convert.getElementFormat(this.list[this.TYPE_UPDATE][id])
		});
		delete this.list[this.TYPE_UPDATE][id];
	}
	if (listUpdate.length > 0)
	{
		dialogList.updateItems(listUpdate);
	}

	if (listAdd.length > 0 || listUpdate.length > 0)
	{
		console.info('RecentList.queue.worker: added - '+listAdd.length+' / updated - '+listUpdate.length+' ('+(new Date() - executeTime)+'ms)', {add: listAdd, update: listUpdate});
		this.base.updateCounter(false);
		this.cache.update({recent: true});
	}

	return true;
};

RecentList.queue.listWorker = function()
{
	let executeTime = new Date();
	let listUpdate = [];
	for (let i=0, l=this.base.list.length; i<l; i++)
	{
		if(!this.base.list[i] || !this.base.list[i].runtime)
		{
			continue;
		}

		if (this.base.list[i].type != 'user')
		{
			continue;
		}

		let updateNeeded = false;

		if (this.base.list[i].runtime.status != this.convert.getUserImageCode(this.base.list[i]))
		{
			updateNeeded = true;
		}

		if (updateNeeded)
		{
			this.add(this.TYPE_UPDATE, this.base.list[i].id, this.base.list[i]);
			listUpdate.push(this.base.list[i].id);
		}
	}

	if (listUpdate.length > 0)
	{
		this.cache.update({recent: true});
	}

	executeTime = (new Date() - executeTime);
	if (listUpdate.length > 0 || executeTime > 3000)
	{
		console.info('RecentList.queue.listWorker: need updated elements - '+listUpdate.length+' ('+executeTime+'ms)', listUpdate);
	}

	return true;
};

RecentList.queue.destroy = function()
{
	clearInterval(this.updateIntervalId);
	clearInterval(this.updateListIntervalId);

	return true;
};


/* Time queue API */
RecentList.timer = {};

RecentList.timer.init = function()
{
	this.list = {};

	this.updateInterval = 1000;

	clearInterval(this.updateIntervalId);
	this.updateIntervalId = setInterval(this.worker.bind(this), this.updateInterval)
};

RecentList.timer.start = function(type, id, time, callback, callbackParams)
{
	id = id === null? 'default': id;

	time = parseInt(time);
	if (time <= 0 || id.toString().length <= 0)
	{
		return false;
	}

	if (typeof this.list[type] == 'undefined')
	{
		this.list[type] = {};
	}

	this.list[type][id] = {
		'dateStop': new Date().getTime()+time,
		'callback': typeof callback == 'function'? callback: function() {},
		'callbackParams': typeof callbackParams == 'undefined'? {}: callbackParams
	};

	return true;
};

RecentList.timer.stop = function(type, id, skipCallback)
{
	id = id === null? 'default': id;

	if (id.toString().length <= 0 || typeof this.list[type] == 'undefined')
	{
		return false;
	}

	if (!this.list[type][id])
	{
		return true;
	}

	if (skipCallback !== true)
	{
		this.list[type][id]['callback'](id, this.list[type][id]['callbackParams']);
	}

	delete this.list[type][id];

	return true;
};

RecentList.timer.stopAll = function(skipCallback)
{
	for (let type in this.list)
	{
		if (this.list.hasOwnProperty(type))
		{
			for (let id in this.list[type])
			{
				if(this.list[type].hasOwnProperty(id))
				{
					this.stop(type, id, skipCallback);
				}
			}
		}
	}
	return true;
};

RecentList.timer.worker = function()
{
	for (let type in this.list)
	{
		if (!this.list.hasOwnProperty(type))
		{
			continue;
		}
		for (let id in this.list[type])
		{
			if(!this.list[type].hasOwnProperty(id) || this.list[type][id]['dateStop'] > new Date())
			{
				continue;
			}
			this.stop(type, id);
		}
	}
	return true;
};

RecentList.timer.destroy = function()
{
	clearInterval(this.updateIntervalId);
	this.stopAll(true);
	return true;
};


/* Notify API */
RecentList.notify = {};

RecentList.notify.init = function ()
{
	this.counter = 0;
	this.show = false;

	BX.addCustomEvent("onNotificationsOpen", this.onNotificationsOpen.bind(this));
};

RecentList.notify.refresh = function()
{
	BX.postWebEvent("onBeforeNotificationsReload", {});
	Application.refreshNotifications();

	return true;
};

RecentList.notify.read = function(id)
{
	id = parseInt(id);
	if (id <= 0)
		return false;

	BX.rest.callMethod('im.notify.read', {'ID': id});

	return true;
};

RecentList.notify.onNotificationsOpen = function(params)
{
	console.info('RecentList.notify: window is open', params);

	this.counter = 0;
	this.base.updateCounter(false);
};


/* Actions API */
RecentList.action = {};

RecentList.action.init = function()
{
	dialogList.setPreviewUrlProvider(this.showPreview.bind(this));
};

RecentList.action.showPreview = function(listElement)
{
	if (listElement.params.type === "user")
	{
		return "/mobile/users/?user_id=" + listElement.params.id;
	}

	return "";
};

RecentList.action.pin = function(elementId, active)
{
	let element = this.base.getElement(elementId, true);

	active = active === true;

	this.base.updateElement(elementId, {
		pinned: active,
	}, true);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.updateElement(id, {
			pinned: params.pinned,
		}, true);
	}, {pinned: element.pinned});

	BX.rest.callMethod('im.recent.pin', {'DIALOG_ID': elementId, 'ACTION': active? 'Y': 'N'})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{
				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.mute = function(elementId, active)
{
	let element = this.base.getElement(elementId, true);
	if (element.type != 'chat' || element.blocked === true)
	{
		return false;
	}

	active = active === true;

	let muteList = Utils.objectClone(element.chat.mute_list);
	muteList[this.base.userId] = active;

	this.base.updateElement(elementId, {
		chat: {mute_list: muteList},
	}, true);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.updateElement(id, {
			chat: {mute_list: params.mute_list},
		}, true);
	}, {mute_list: element.chat.mute_list});

	BX.rest.callMethod('im.chat.mute', {'CHAT_ID': element.chat.id, 'ACTION': active? 'Y': 'N'})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{
				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.hide = function(elementId)
{
	let element = this.base.getElement(elementId, true);

	this.base.deleteElement(elementId);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.setElement(id, params, true);
	}, element);

	BX.rest.callMethod('im.recent.hide', {'DIALOG_ID': elementId})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{

				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.operatorAnswer = function(elementId)
{
	let element = this.base.getElement(elementId, true);
	if (element.type != 'chat' || element.blocked === true)
	{
		return false;
	}

	this.base.updateElement(elementId, {
		chat: {owner: this.base.userId},
		message: {
			date: new Date(),
			text: BX.message("IMOL_CHAT_ANSWER_"+this.base.userData.gender).replace('#USER#', this.base.userData.name)
		},
		counter: 0
	}, true);

	this.base.openDialog(elementId);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.updateElement(id, {
			chat: {owner: params.owner},
			message: {date: params.messageDate, text: params.messageText},
			counter: params.counter
		}, true);
	}, {
		owner: element.chat.owner,
		counter: element.counter,
		messageDate: element.message.date,
		messageText: element.message.text
	});

	BX.rest.callMethod('imopenlines.operator.answer', {'CHAT_ID': element.chat.id})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{
				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.operatorSkip = function(elementId)
{
	let element = this.base.getElement(elementId, true);
	if (element.type != 'chat' || element.blocked === true)
	{
		return false;
	}

	this.base.deleteElement(elementId);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.setElement(id, params, true);
	}, element);

	BX.rest.callMethod('imopenlines.operator.skip', {'CHAT_ID': element.chat.id})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{

				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.operatorSpam = function(elementId)
{
	let element = this.base.getElement(elementId, true);
	if (element.type != 'chat' || element.blocked === true)
	{
		return false;
	}

	this.base.deleteElement(elementId);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.setElement(id, params, true);
	}, element);

	BX.rest.callMethod('imopenlines.operator.spam', {'CHAT_ID': element.chat.id})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{

				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.operatorFinish = function(elementId)
{
	let element = this.base.getElement(elementId, true);
	if (element.type != 'chat' || element.blocked === true)
	{
		return false;
	}

	this.base.deleteElement(elementId);

	this.base.blockElement(elementId, true, (id, params) => {
		this.base.setElement(id, params, true);
	}, element);

	BX.rest.callMethod('imopenlines.operator.finish', {'CHAT_ID': element.chat.id})
		.then((result) =>
		{
			if (result.data()) // TODO ALERT IF ERROR
			{
				this.base.unblockElement(elementId);
			}
			else
			{

				this.base.unblockElement(elementId, true);
			}
		})
		.catch(() =>
		{
			this.base.unblockElement(elementId, true);
		});

	return true;
};

RecentList.action.writing = function(elementId, action)
{
	if (action)
	{
		RecentList.updateElement(elementId, {writing: true});
		this.timer.start('writing', elementId, 29500, (id) => {
			RecentList.updateElement(id, {writing: false});
		});
	}
	else
	{
		this.timer.stop('writing', elementId)
	}
};



/* Event API */
RecentList.event = {};

RecentList.event.init = function ()
{
	this.debug = false;
	this.handlersList = {
		onItemSelected : this.onItemSelected,
		onItemAction : this.onItemAction,
		onRefresh : this.onRefresh,
		onScrollAtTheTop : this.onScrollAtTheTop,
		onSearchShow : this.onSearchShow,
		onSearchHide : this.onSearchHide,
		onUserTypeText : this.onSearchTextType,
		onSearchItemSelected : this.onSearchItemSelected,
	};

	dialogList.setListener(this.router.bind(this));
};

RecentList.event.router = function(eventName, listElement)
{
	if (this.handlersList[eventName])
	{
		if (eventName != 'onUserTypeText')
		{
			console.log('RecentList.event.router: catch event - '+eventName, listElement);
		}
		this.handlersList[eventName].apply(this, [listElement])
	}
	else if (this.debug)
	{
		console.info('RecentList.event.router: skipped event - '+eventName, listElement);
	}
};

RecentList.event.onItemSelected = function(listElement)
{
	if (listElement.params.type == 'openSearch')
	{
		if (this.base.isRecent() && typeof dialogList.showSearchBar != 'undefined')
		{
			console.info('RecentList.event.onItemSelected: open search dialog');
			dialogList.showSearchBar();
		}
	}
	else
	{
		console.info('RecentList.event.onItemSelected: open dialog', listElement.params.id);
		this.base.openDialog(listElement.params.id);
	}
};

RecentList.event.onItemAction = function(listElement)
{
	if (listElement.action.identifier === "hide")
	{
		this.action.hide(listElement.item.params.id);
	}
	else if (listElement.action.identifier === "call")
	{
		this.base.callUser(listElement.item.params.id);
	}
	else if (listElement.action.identifier === "pin")
	{
		this.action.pin(listElement.item.params.id, true)
	}
	else if (listElement.action.identifier === "unpin")
	{
		this.action.pin(listElement.item.params.id, false);
	}
	else if (listElement.action.identifier === "mute")
	{
		this.action.mute(listElement.item.params.id, true)
	}
	else if (listElement.action.identifier === "unmute")
	{
		this.action.mute(listElement.item.params.id, false);
	}
	else if (listElement.action.identifier === "chatinfo")
	{
		PageManager.openPage({"url" : "/mobile/im/chat.php?chat_id=" + listElement.item.params.id.substr(4)});
	}
	else if (listElement.action.identifier === "operatorAnswer")
	{
		this.action.operatorAnswer(listElement.item.params.id, false);
	}
	else if (listElement.action.identifier === "operatorSkip")
	{
		this.action.operatorSkip(listElement.item.params.id, false);
	}
	else if (listElement.action.identifier === "operatorFinish")
	{
		this.action.operatorFinish(listElement.item.params.id, false);
	}
	else if (listElement.action.identifier === "operatorSpam")
	{
		this.action.operatorSpam(listElement.item.params.id, false);
	}
	else
	{
		this.search.onItemAction(listElement);
	}
};

RecentList.event.onRefresh = function()
{
	//reloadAllScripts();
	this.base.refresh();
};

RecentList.event.onScrollAtTheTop = function()
{
	if (typeof dialogList.toggleSearchBar != "function")
	{
		return false;
	}

	dialogList.toggleSearchBar();

	return true;
};

RecentList.event.onSearchShow = function()
{
	this.search.drawIntro();
};

RecentList.event.onSearchHide = function()
{
	this.search.clear();
};

RecentList.event.onSearchTextType = function(search)
{
	this.search.find(search? search.text: '');
};

RecentList.event.onSearchItemSelected = function(listElement)
{
	this.search.onSearchItemSelected(listElement);
};


/* Event API */
RecentList.search = {
	TYPE_USER: 'user',
	TYPE_CHAT: 'chat',
	MORE_TYPE_CACHE: 'cache',
	MORE_TYPE_EXTERNAL: 'external'
};

RecentList.search.init = function ()
{
	this.debug = false;

	this.recent = [];

	this.cacheIndex = {};
	this.cacheList = [];
	this.cacheRequest = {};

	this.limit = 30;

	this.result = {
		text: '',
		progressIcon: {},
		moreButton: {},
		offset: {},
		items: [],
		itemsIndex: {},
		types: {},
	};
};

RecentList.search.drawIntro = function()
{
	let items = [];
	let sections = [];

	let employees = [];
	let employeesIndex = {};
	let chats = [];

	if (this.base.list.length > 0)
	{
		this.base.list.map(el =>
		{
			if (!el)
			{
				return false;
			}
			if (el.type == 'user')
			{
				let element = this.convert.getElementFormat(el);
				element.title = el.user.first_name? el.user.first_name: el.user.name;
				element.params.action = 'item';
				employees.push(element);
				employeesIndex[el.id] = true;
			}
			else if (false)
			{
				let element = this.convert.getElementFormat(el);
				element.params.action = 'item';
				chats.push(element);
			}

			return true;
		});
	}

	this.base.colleaguesList.map(el =>
	{
		if (!el || employeesIndex[el.id])
		{
			return false;
		}

		let element = this.convert.getElementFormatByEntity('user', el);
		element.title = el.first_name? el.first_name: el.name;
		element.params.action = 'item';
		employees.push(element);

		return true;
	});

	employees = employees.filter((element) => element.id != this.base.userId);

	if (employees.length)
	{
		sections.push({title : BX.message("SEARCH_EMPLOYEES"), id : "user", backgroundColor : "#FFFFFF"});
		items.push({type : "carousel", sectionCode : "user", childItems : employees});
	}

	if (chats.length)
	{
		sections.push({title : BX.message("SEARCH_CHATS"), id : "chat", backgroundColor : "#FFFFFF"});
		items.push({type : "carousel", sectionCode : "chat", childItems : chats});
	}

	if (this.recent.length)
	{
		let recent = [];
		this.recent.map(element =>
		{
			if (!element)
			{
				return false;
			}

			recent.push(this.convert.getSearchElementFormat(element, true));
		});

		items = items.concat(recent);
		sections.push({title : BX.message("SEARCH_RECENT"), id : "recent", backgroundColor : "#FFFFFF"});
	}

	dialogList.setSearchResultItems(items, sections);
};

RecentList.search.find = function(text, offset)
{
	text = text.toString().trim();

	if (!text)
	{
		this.clear();
		this.drawIntro();
	}
	else if (text.length >= 3)
	{
		this.timer.stop('search', this.TYPE_USER, true);
		this.base.requestAbort('search-'+this.TYPE_USER);

		this.setMoreButton(this.TYPE_USER, false);
		this.setMoreButton(this.TYPE_CHAT, false);

		this.setText(text);
		this.setItems([]);

		this.findLocal(text, offset).then(result => {
			this.setItems(result.items);

			this.setProgressIcon(this.TYPE_USER, true);

			this.drawSearch();

			this.findExternal(this.TYPE_USER, result.text, result.offset);
		});
	}
	else
	{
		this.timer.stop('search', this.TYPE_USER, true);
		this.base.requestAbort('search-'+this.TYPE_USER);

		this.setMoreButton(this.TYPE_USER, false);
		this.setMoreButton(this.TYPE_CHAT, false);

		this.setText(text);

		this.setItems([]);

		this.findLocal(text, offset).then(result => {
			this.setItems(result.items);

			this.setProgressIcon(this.TYPE_USER, false);
			this.setProgressIcon(this.TYPE_CHAT, false);

			this.drawSearch();
		});
	}

	return true;
};

RecentList.search.findLocal = function(text, offset)
{
	offset = offset || 0;

	let cachePromise = new BX.Promise();

	let recentUserItems = [];
	let recentChatItems = [];

	this.recent.concat(this.base.list).concat(this.cacheList).map(element => {
		if (!element)
			return true;

		if (element.type == 'user')
		{
			recentUserItems.push(element.user);
		}
		else
		{
			recentChatItems.push(element.chat);
		}
	});

	recentUserItems = recentUserItems.concat(this.base.colleaguesList);

	let userItems = this.filter(text, ['name', 'work_position'], [recentUserItems]);
	let chatItems = this.filter(text, ['name'], [recentChatItems]);

	cachePromise.fulfill({
		text: text,
		offset: offset,
		items: userItems.slice(0, this.limit).concat(chatItems.slice(0, this.limit))
	});

	return cachePromise;
};

RecentList.search.findExternal = function(type, text, offset)
{
	offset = offset || 0;

	let promise = new BX.Promise();
	promise.then(result =>
	{
		this.cacheRequest[result.type][result.text][result.limit][result.offset] = result;

		let items = [];
		if (result.count)
		{
			for (let i in result.items)
			{
				if(!result.items.hasOwnProperty(i)) { continue; }
				items.push(result.items[i]);
			}

			this.indexItems(result.type, items);

			items = this.filter(result.text, ['name', 'work_position'], [items]).filter((element) => {
				return typeof this.result.itemsIndex[element.id] == 'undefined'
			});
			if (items.length > 0 || result.offset == 0)
			{
				this.appendItems(items, false);
				this.setMoreButton(result.type, result.hasMore);
			}
			else
			{
				this.setMoreButton(result.type, result.hasMore, BX.message('SEARCH_MORE_READY'));
			}

			this.setProgressIcon(result.type, false);

			this.setOffset(result.type, result.offset+result.limit);
		}
		else
		{
			this.setProgressIcon(result.type, false);
			this.setMoreButton(result.type, false);
		}
		this.drawSearch();
	}).catch(result => {

		if (result.error.ex.error == 'REQUEST_CANCELED')
		{
			console.info("RecentList.search.find: execute request canceled by user", result.error.ex);
		}
		else
		{
			console.error("RecentList.search.find: error has occurred ", result.error);

			this.setProgressIcon(result.type, false);
			this.setMoreButton(result.type, false);
			this.drawSearch();
		}
	});

	this.timer.stop('search', type, true);

	if (typeof this.cacheRequest[type] == 'undefined')
	{
		this.cacheRequest[type] = {};
	}
	if (typeof this.cacheRequest[type][text] == 'undefined')
	{
		this.cacheRequest[type][text] = {};
	}
	if (typeof this.cacheRequest[type][text][this.limit] == 'undefined')
	{
		this.cacheRequest[type][text][this.limit] = {};
	}
	if (typeof this.cacheRequest[type][text][this.limit][offset] == 'undefined')
	{
		this.cacheRequest[type][text][this.limit][offset] = false;
	}

	if (this.cacheRequest[type][text][this.limit][offset])
	{
		promise.fulfill(this.cacheRequest[type][text][this.limit][offset]);
	}
	else
	{
		this.timer.start('search', type, 1000, (id, params) =>
		{
			this.restRequest(params.type, params.text, params.limit, params.offset, params.promise)
		}, {
			type: type.toString(),
			text: text.toString(),
			limit: this.limit.toString(),
			offset: offset.toString(),
			promise: promise
		});
	}

	return true;
};

RecentList.search.restRequest = function(type, text, limit, offset, promise)
{
	limit = parseInt(limit) || 15;
	offset = parseInt(offset) || 0;
	promise = promise || new BX.Promise();
	type = type == 'chat'? 'chat': 'user';

	this.base.requestAbort('search-'+type);

	BX.rest.callMethod('im.search.'+type, {'FIND': text, 'LIMIT': limit, 'OFFSET': offset}, null, (xhr) => {
		this.base.requestRegister('search-'+type, xhr);
	}).then((result) =>
	{
		let type = result.query.method == 'im.search.user'? this.TYPE_USER: this.TYPE_CHAT;

		this.base.requestUnregister('search-'+type);
		if (result.data())
		{
			let hasMore = parseInt(result.total())-(parseInt(result.query.data.OFFSET)+parseInt(result.query.data.LIMIT)) > 0;

			let counter = 0;
			for (let i in result.data())
			{
				counter++;
			}

			promise.fulfill({
				type: type,
				text: result.query.data.FIND,
				offset: result.query.data.OFFSET,
				limit: result.query.data.LIMIT,
				total: result.total()? result.total(): 0,
				count: counter,
				hasMore: hasMore,
				items: result.data(),
			});
		}
		else
		{
			promise.reject({
				type: result.query.method == 'im.user.list'? this.TYPE_USER: this.TYPE_CHAT,
				text: result.query.TEXT,
				offset: result.query.OFFSET,
				limit: result.query.LIMIT,
				error: result.error()
			});
		}
	})
	.catch((result) =>
	{
		let type = result.query.method == 'im.user.list'? this.TYPE_USER: this.TYPE_CHAT;

		this.base.requestUnregister('search-'+type);

		promise.reject({
			type: type,
			text: result.query.TEXT,
			offset: result.query.OFFSET,
			limit: result.query.LIMIT,
			error: result.error()
		});
	});

	return promise;
};

RecentList.search.filter = function(text, fields, sources)
{
	let result = [];
	let foundElementId = {};

	sources.forEach((source) =>
	{
		let sourceResult = source.filter(item =>
		{
			let fieldResult = false;
			fields.forEach((field) =>
			{
				if (item[field] && !foundElementId[item.id])
				{
					if (item[field].toUpperCase().indexOf(text.toUpperCase()) == 0)
					{
						fieldResult = true;
						foundElementId[item.id] = true;
					}
					else
					{
						item[field].toUpperCase().split(' ').forEach((word) =>
						{
							if (word.indexOf(text.toUpperCase()) == 0)
							{
								fieldResult = true;
								foundElementId[item.id] = true;
							}
						});
					}
				}
			});

			return fieldResult;
		});

		result = result.concat(sourceResult);
	});

	return result.slice(0, this.limit);
};

RecentList.search.updateElement = function(dialogId, params)
{
	if (typeof this.cacheIndex[dialogId] != 'undefined')
	{
		this.cacheList[this.cacheIndex[dialogId]] = Utils.objectMerge(this.cacheList[this.cacheIndex[dialogId]], params);
	}

	let index = this.recent.findIndex((listElement) => listElement && listElement.id == dialogId);
	if (index > -1)
	{
		this.recent[index] = Utils.objectMerge(this.recent[index], params);
	}

	return true;
};

RecentList.search.deleteElement = function(dialogId)
{
	if (typeof this.cacheIndex[dialogId] != 'undefined')
	{
		delete this.cacheList[this.cacheIndex[dialogId]];
		delete this.cacheIndex[dialogId];
	}

	let index = this.recent.findIndex((listElement) => listElement && listElement.id == dialogId);
	if (index > -1)
	{
		delete this.recent[index];
	}

	return true;
};

RecentList.search.indexItems = function(type, items)
{
	items.map((element) => {
		let item = {
			avatar: {},
			user: {id: 0}
		};
		if (type == 'chat')
		{
			item.type = 'chat';
			item.id = 'chat'+element.id;

			element.id = parseInt(element.id);
			element.date_create = new Date(element.date_create);
			item.chat = element.chat;

			item.avatar.url = element.avatar;
			item.avatar.color = element.color;
			item.title = element.name;
		}
		else
		{
			item.type = 'user';
			item.id = parseInt(element.id);

			item.user = element = this.pull.getUserDataFormat(element);

			item.avatar.url = element.avatar;
			item.avatar.color = element.color;
			item.title = element.name;
		}

		if (typeof this.cacheIndex[item.id] != 'undefined')
		{
			this.cacheList[this.cacheIndex[item.id]] = item;
		}
		else
		{
			this.cacheIndex[item.id] = this.cacheList.length;
			this.cacheList.push(item);
		}
	});

	return true;
};

RecentList.search.clear = function(redraw)
{
	this.timer.stop('search', this.TYPE_USER, true);

	this.base.requestAbort('search-'+this.TYPE_USER);
	this.base.requestAbort('search-'+this.TYPE_CHAT);

	this.setText('');
	this.setProgressIcon(false);
	this.setMoreButton(this.TYPE_USER, false);
	this.setMoreButton(this.TYPE_CHAT, false);
	this.setOffset(this.TYPE_USER, 0);
	this.setOffset(this.TYPE_CHAT, 0);
	this.setItems([]);

	if (redraw)
	{
		this.drawSearch();
	}

	return true;
};

RecentList.search.setText = function(text)
{
	this.result.text = text;
};

RecentList.search.setProgressIcon = function(type, active)
{
	this.result.progressIcon[type] = active == true;
};

RecentList.search.setMoreButton = function(type, active, text)
{
	this.result.moreButton[type] = {active: active == true, text: text? text: BX.message("SEARCH_MORE")};
};

RecentList.search.setOffset = function(type, offset)
{
	this.result.offset[type] = offset || 0;
};

RecentList.search.setItems = function(items, filter)
{
	let result = [];
	if (items.length <= 0)
	{
		this.result.itemsIndex = {};
	}
	else
	{
		if (filter !== false)
		{
			items = items.filter((element) => {
				return typeof this.result.itemsIndex[element.id] == 'undefined'
			})
		}
		items.map((element) => {
			this.result.itemsIndex[element.id] = true;
			result.push(this.convert.getSearchElementFormat(element));
		});
	}

	this.result.items = result;
};

RecentList.search.appendItems = function(items, filter)
{
	let result = [];
	if (filter !== false)
	{
		items = items.filter((element) => {
			return typeof this.result.itemsIndex[element.id] == 'undefined'
		})
	}
	items.map((element) => {
		this.result.itemsIndex[element.id] = true;
		result.push(this.convert.getSearchElementFormat(element));
	});

	this.result.items = this.result.items.concat(result);
};

RecentList.search.drawSearch = function()
{
	let executeTime = new Date();

	let items = Utils.objectClone(this.result.items);

	if (this.result.moreButton[this.TYPE_USER] && this.result.moreButton[this.TYPE_USER]['active'])
	{
		items.push({title : this.result.moreButton[this.TYPE_USER]['text'], type : "button", sectionCode: 'user', params: { action: 'more', value: 'user'}});
	}

	if (this.result.moreButton[this.TYPE_CHAT] && this.result.moreButton[this.TYPE_CHAT]['active'])
	{
		items.push({title : this.result.moreButton[this.TYPE_CHAT]['text'], type : "button", sectionCode: 'chat', params: { action: 'more', value: 'chat'}});
	}

	let showProgress = false;
	if (this.result.progressIcon[this.TYPE_USER] || this.result.progressIcon[this.TYPE_CHAT])
	{
		showProgress = true;
		if (!this.result.types[this.TYPE_USER] && !this.result.types[this.TYPE_CHAT])
		{
			items.push({title : BX.message("SEARCH"), type : "loading", unselectable: true, params: { action: 'progress'}});
		}
		else
		{
			if (this.result.progressIcon[this.TYPE_USER])
			{
				items.push({title : BX.message("SEARCH"), type : "loading", unselectable: true, sectionCode: 'user', params: { action: 'progress'}});
			}
			if (this.result.progressIcon[this.TYPE_CHAT])
			{
				items.push({title : BX.message("SEARCH"), type : "loading", unselectable: true, sectionCode: 'chat', params: { action: 'progress'}});
			}
		}
	}

	if (!showProgress && this.result.items.length <= 0)
	{
		items.push(
			{title : BX.message("SEARCH_EMPTY").replace("#TEXT#", this.result.text), type:"button", unselectable: true, params: { action: 'empty'}}
		);
	}

	let section = [];

	this.result.types[this.TYPE_USER] = false;
	this.result.types[this.TYPE_CHAT] = false;

	for (let i=0, l=items.length; i<l; i++)
	{
		if (items[i].sectionCode == 'user')
		{
			this.result.types[this.TYPE_USER] = true;
		}
		else if (items[i].sectionCode == 'chat')
		{
			this.result.types[this.TYPE_CHAT] = true;
		}
	}

	if (this.result.types[this.TYPE_USER])
	{
		section.push({title : BX.message("SEARCH_EMPLOYEES"), id : "user", backgroundColor : "#FFFFFF"});
	}
	if (this.result.types[this.TYPE_CHAT])
	{
		section.push({title : BX.message("SEARCH_CHATS"), id : "chat", backgroundColor : "#FFFFFF"});
	}

	dialogList.setSearchResultItems(items, section);

	console.info("RecentList.search.drawSearch: update search results - "+items.length+" elements ("+(new Date() - executeTime)+'ms)', this.result.text);

	return true;
};

RecentList.search.lastSearchAdd = function(dialogId)
{
	let isExists = !this.recent.every(element => !(element.id == dialogId));
	if (isExists)
	{
		return true;
	}

	let item = false;
	if (typeof this.cacheIndex[dialogId] != 'undefined')
	{
		item = this.cacheList[this.cacheIndex[dialogId]];
	}
	else
	{
		item = this.base.getElement(dialogId);
	}

	if (!item)
	{
		return false;
	}

	this.recent.unshift(Utils.objectClone(item));
	this.cache.update({lastSearch: true});

	BX.rest.callMethod('im.search.last.add', {'DIALOG_ID': dialogId});

	return true;
};

RecentList.search.lastSearchDelete = function(dialogId)
{
	BX.rest.callMethod('im.search.last.delete', {'DIALOG_ID': dialogId})
		.then((result) =>
		{
			if (result.data())
			{
				this.recent.every((element, index) => {
					if (element.id == dialogId)
					{
						delete this.recent[index];
						return false;
					}
					return true;
				});
				this.cache.update({lastSearch: true});
			}
		});
};


RecentList.search.onSearchItemSelected = function(listElement)
{
	if (listElement.params.action == 'more')
	{
		let type = listElement.params.value;

		this.setProgressIcon(type, true);
		this.setMoreButton(type, false);
		this.drawSearch();

		this.findExternal(type, this.result.text, this.result.offset[listElement.params.value]);
	}
	else if (listElement.params.action == 'item')
	{
		let dialogId = (listElement.params.type === "chat"? "chat": "") + listElement.params.id;
		this.base.openDialog(dialogId);
		this.lastSearchAdd(dialogId);
	}

	return true;
};

RecentList.search.onItemAction = function(listElement)
{
	if (listElement.action.identifier === "delete")
	{
		this.lastSearchDelete(listElement.item.params.id)
	}
};



/* Utils API */
var Utils = {};

Utils.isObjectChanged = function(currentProperties, newProperties)
{
	for (let name in newProperties)
	{
		if(!newProperties.hasOwnProperty(name))
		{
			continue;
		}

		if (typeof currentProperties[name] == 'undefined')
		{
			return true;
		}

		if (BX.type.isPlainObject(newProperties[name]))
		{
			if (!BX.type.isPlainObject(currentProperties[name]))
			{
				return true;
			}

			if (this.isObjectChanged(currentProperties[name], newProperties[name]) === true)
			{
				return true;
			}
		}
		else if (currentProperties[name] !== newProperties[name])
		{
			return true;
		}
	}

	return false;
};

Utils.objectMerge = function(currentProperties, newProperties)
{
	for (let name in newProperties)
	{
		if(!newProperties.hasOwnProperty(name))
		{
			continue;
		}
		if (BX.type.isPlainObject(newProperties[name]))
		{
			if (!BX.type.isPlainObject(currentProperties[name]))
			{
				currentProperties[name] = {};
			}
			currentProperties[name] = this.objectMerge(currentProperties[name], newProperties[name]);
		}
		else
		{
			currentProperties[name] = newProperties[name];
		}
	}

	return currentProperties;
};

Utils.objectClone = function(properties)
{
	let newProperties = {};
	if (properties === null)
		return null;

	if (typeof properties == 'object')
	{
		if (BX.type.isArray(properties))
		{
			newProperties = [];
			for (let i=0, l=properties.length; i<l; i++)
			{
				if (typeof properties[i] == "object")
				{
					newProperties[i] = Utils.objectClone(properties[i]);
				}
				else
				{
					newProperties[i] = properties[i];
				}
			}
		}
		else
		{
			newProperties =  {};
			if (properties.constructor)
			{
				if (BX.type.isDate(properties))
				{
					newProperties = new Date(properties);
				}
				else
				{
					newProperties = new properties.constructor();
				}
			}

			for (let i in properties)
			{
				if (!properties.hasOwnProperty(i))
				{
					continue;
				}
				if (typeof properties[i] == "object")
				{
					newProperties[i] = Utils.objectClone(properties[i]);
				}
				else
				{
					newProperties[i] = properties[i];
				}
			}
		}
	}
	else
	{
		newProperties = properties;
	}

	return newProperties;
};

Utils.getAvatar = function(url)
{
	if (url == '' || url.indexOf('/thurly/js/im/images/blank.gif') >= 0)
	{
		return '';
	}

	url = url.indexOf('http') === 0? url: currentDomain+url;

	return encodeURI(url);
};

Utils.getTimestamp = function(atom)
{
	return Math.round(new Date(atom).getTime()/1000);
};

Utils.htmlspecialcharsback = function(str)
{
	if(!str || !str.replace) return str;

	return str.replace(/\&quot;/g, '"').replace(/&#39;/g, "'").replace(/\&lt;/g, '<').replace(/\&gt;/g, '>').replace(/\&amp;/g, '&').replace(/\&nbsp;/g, ' ');
};


/* MessengerCommon API */
var MessengerCommon = {};

MessengerCommon.getUserStatus = function(userData, onlyStatus) // after change this code, sync with IM and MOBILE
{
	onlyStatus = onlyStatus !== false;

	var online = this.getOnlineData(userData);

	var status = '';
	var statusText = '';
	var originStatus = '';
	var originStatusText = '';
	if (!userData)
	{
		status = 'guest';
		statusText = BX.message('IM_STATUS_GUEST');
	}
	else if (userData.network)
	{
		status = 'network';
		statusText = BX.message('IM_STATUS_NETWORK');
	}
	else if (userData.bot)
	{
		status = 'bot';
		statusText = BX.message('IM_STATUS_BOT');
	}
	else if (userData.connector)
	{
		status = userData.status == 'offline'? 'lines': 'lines-online';
		statusText = BX.message('IM_CL_USER_LINES');
	}
	else if (userData.status == 'guest')
	{
		status = 'guest';
		statusText = BX.message('IM_STATUS_GUEST');
	}
	else if (this.getCurrentUser() == userData.id)
	{
		status = userData.status? userData.status.toString(): '';
		statusText = status? BX.message('IM_STATUS_'+status.toUpperCase()): '';
	}
	else if (!online.isOnline)
	{
		status = 'offline';
		statusText = BX.message('IM_STATUS_OFFLINE');
	}
	else if (this.getUserMobileStatus(userData))
	{
		status = 'mobile';
		statusText = BX.message('IM_STATUS_MOBILE');
	}
	else if (this.getUserIdleStatus(userData, online))
	{
		status = 'idle';
		statusText = BX.message('IM_STATUS_AWAY_TITLE').replace('#TIME#', this.getUserIdle(userData));
	}
	else
	{
		status = userData.status? userData.status.toString(): '';
		statusText = BX.message('IM_STATUS_'+status.toUpperCase());
	}

	if (this.isBirthday(userData.birthday) && (userData.status == 'online' || !online.isOnline))
	{
		var originStatus = status;
		var originStatusText = statusText;

		status = 'birthday';
		if (online.isOnline)
		{
			statusText = BX.message('IM_M_BIRTHDAY_MESSAGE_SHORT');
		}
		else
		{
			statusText = BX.message('IM_STATUS_OFFLINE');
		}
	}
	else if (userData.absent)
	{
		var originStatus = status;
		var originStatusText = statusText;

		status = 'vacation';
		if (online.isOnline)
		{
			statusText = BX.message('IM_STATUS_ONLINE');
		}
		else
		{
			statusText = BX.message('IM_STATUS_VACATION');
		}
	}

	return onlyStatus? status: {
		status: status,
		statusText: statusText,
		originStatus: originStatus? originStatus: status,
		originStatusText: originStatusText? originStatusText: statusText,
	};
};

MessengerCommon.getUserMobileStatus = function(userData) // after change this code, sync with IM and MOBILE
{
	if (!userData)
		return false;

	var status = false;
	var mobile_last_date = userData.mobile_last_date;
	var last_activity_date = userData.last_activity_date;
	if (
		(new Date())-mobile_last_date < BX.user.getSecondsForLimitOnline()*1000
		&& last_activity_date-mobile_last_date < 300*1000
	)
	{
		status = true;
	}

	return status;
};

MessengerCommon.getUserIdleStatus = function(userData, online) // after change this code, sync with IM and MOBILE
{
	if (!userData)
		return '';

	online = online? online: BX.user.getOnlineStatus(userData.last_activity_date);

	return userData.idle && online.isOnline;
};

MessengerCommon.getUserPosition = function(userData, recent) // after change this code, sync with IM and MOBILE
{
	recent = recent === true;

	if (!userData)
		return '';

	var position = '';
	if (recent && userData.last_activity_date && !(userData.bot || userData.network))
	{
		return this.getUserLastDate(userData);
	}
	else if(userData.work_position)
	{
		position = userData.work_position;
	}
	else if (userData.extranet || userData.network)
	{
		position = BX.message('IM_CL_USER_EXTRANET');
	}
	else if (userData.bot)
	{
		position = BX.message('IM_CL_BOT');
	}
	else
	{
		position = this.isIntranet()? BX.message('IM_CL_USER'): BX.message('IM_CL_USER_B24');
	}

	return position
};

MessengerCommon.linesGetSession = function(chatData) // after change this code, sync with IM and MOBILE
{
	var session = null;
	if (!chatData || chatData.type != "lines")
			return session;

	session = {};
	session.source = this.linesGetSource(chatData);

	var source = chatData.entity_id.toString().split('|');

	session.connector = source[0];
	session.lineId = source[1];
	session.canVoteHead = this.linesCanVoteAsHead(source[1]);

	var sessionData = chatData.entity_data_1.toString().split('|');

	session.crm = typeof(sessionData[0]) != 'undefined' && sessionData[0] == 'Y'? 'Y': 'N';
	session.crmEntityType = typeof(sessionData[1]) != 'undefined'? sessionData[1]: 'NONE';
	session.crmEntityId = typeof(sessionData[2]) != 'undefined'? sessionData[2]: 0;
	session.crmLink = '';
	session.pin = typeof(sessionData[3]) != 'undefined' && sessionData[3] == 'Y'? 'Y': 'N';
	session.wait = typeof(sessionData[4]) != 'undefined' && sessionData[4] == 'Y'? 'Y': 'N';
	session.id = typeof(sessionData[5]) != 'undefined'? parseInt(sessionData[5]): Math.round(new Date()/1000)+chatData.id;
	session.dateCreate = typeof(sessionData[6]) != 'undefined' || sessionData[6] > 0? parseInt(sessionData[6]): session.id;

	if (session.crmEntityType != 'NONE')
	{
		session.crmLink = this.linesGetCrmPath(session.crmEntityType, session.crmEntityId);
	}

	return session;
};

MessengerCommon.linesGetSource = function(chatData) // after change this code, sync with IM and MOBILE
{
	var sourceId = '';
	if (!chatData || !(chatData.type == 'livechat' || chatData.type == 'lines'))
		return sourceId;

	if (chatData.type == 'livechat')
	{
		sourceId = 'livechat';
	}
	else
	{
		sourceId = (chatData.entity_id.toString().split('|'))[0];
	}

	if (sourceId == 'skypebot')
	{
		sourceId = 'skype';
	}
	else
	{
		sourceId = sourceId.replace('.', '_');
	}

	return sourceId;
};

MessengerCommon.isBirthday = function(birthday) // after change this code, sync with IM and MOBILE
{
	var date = new Date();
	var currentDate = ("0" + date.getDate().toString()).substr(-2)+'-'+("0" + (date.getMonth() + 1).toString()).substr(-2);
	return birthday == currentDate;
};

MessengerCommon.purifyText = function(text, params) // after change this code, sync with IM and MOBILE
{
	if (text)
	{
		text = text.toString();
		text = this.trimText(text);

		if (text.indexOf('/me') == 0)
		{
			text = text.substr(4);
		}
		else if (text.indexOf('/loud') == 0)
		{
			text = text.substr(6);
		}
		if (text.substr(-6) == '<br />')
		{
			text = text.substr(0, text.length-6);
		}
		text = text.replace(/<br><br \/>/ig, '<br />');
		text = text.replace(/<br \/><br>/ig, '<br />');
		text = text.replace(/\[[buis]\](.*?)\[\/[buis]\]/ig, '$1');
		text = text.replace(/\[url\](.*?)\[\/url\]/ig, '$1');
		text = text.replace(/\[RATING=([1-5]{1})\]/ig, function(whole, rating) {return '['+BX.message('IM_F_RATING')+'] ';});
		text = text.replace(/\[ATTACH=([0-9]{1,})\]/ig, function(whole, rating) {return '['+BX.message('IM_F_ATTACH')+'] ';});
		text = text.replace(/\[USER=([0-9]{1,})\](.*?)\[\/USER\]/ig, '$2');
		text = text.replace(/\[CHAT=([0-9]{1,})\](.*?)\[\/CHAT\]/ig, '$2');
		text = text.replace(/\[SEND=([0-9]{1,})\](.*?)\[\/SEND\]/ig, '$2');
		text = text.replace(/\[PUT=([0-9]{1,})\](.*?)\[\/PUT\]/ig, '$2');
		text = text.replace(/\[CALL=([0-9]{1,})\](.*?)\[\/CALL\]/ig, '$2');
		text = text.replace(/\[PCH=([0-9]{1,})\](.*?)\[\/PCH\]/ig, '$2');
		text = text.replace(/<img.*?data-code="([^"]*)".*?>/ig, '$1');
		text = text.replace(/<span.*?title="([^"]*)".*?>.*?<\/span>/ig, '($1)');
		text = text.replace(/<img.*?title="([^"]*)".*?>/ig, '($1)');
		text = text.replace(/\[ATTACH=([0-9]{1,})\]/ig, function(whole, command, text) {return command == 10000? '': '['+BX.message('IM_F_ATTACH')+'] ';});
		text = text.replace(/<s>([^"]*)<\/s>/ig, ' ');
		text = text.replace(/\[s\]([^"]*)\[\/s\]/ig, ' ');
		text = text.replace(/\[icon\=([^\]]*)\]/ig, function(whole)
		{
			var title = whole.match(/title\=(.*[^\s\]])/i);
			if (title && title[1])
			{
				title = title[1];
				if (title.indexOf('width=') > -1)
				{
					title = title.substr(0, title.indexOf('width='))
				}
				if (title.indexOf('height=') > -1)
				{
					title = title.substr(0, title.indexOf('height='))
				}
				if (title.indexOf('size=') > -1)
				{
					title = title.substr(0, title.indexOf('size='))
				}
				if (title)
				{
					title = '('+this.trimText(title)+')';
				}
			}
			else
			{
				title = '('+BX.message('IM_M_ICON')+')';
			}
			return title;
		}.bind(this));
		text = text.replace('<br />', ' ').replace(/<\/?[^>]+>/gi, '').replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, " ["+BX.message("IM_M_QUOTE_BLOCK")+"] ");

		text = this.trimText(text);
	}

	if (!text || text.length <= 0)
	{
		if (params && params.FILE_ID && params.FILE_ID.length > 0)
		{
			text = '['+BX.message('IM_F_FILE')+']';
		}
		else if (params && params.ATTACH && params.ATTACH.length > 0)
		{
			text = '['+BX.message('IM_F_ATTACH')+']';
		}
		else
		{
			text = BX.message('IM_M_DELETED');
		}
	}

	return text;
};

MessengerCommon.getOnlineData = function(userData)
{
	let online = {};
	if (!userData)
	{
		return online;
	}

	if (userData.id == this.getCurrentUser())
	{
		userData.last_activity_date = new Date();
		userData.mobile_last_date = new Date(0);
		userData.idle = false;

		RecentList.userData.last_activity_date = userData.last_activity_date;
		RecentList.userData.mobile_last_date = userData.mobile_last_date;
		RecentList.userData.idle = userData.idle;
	}

	online = BX.user.getOnlineStatus(userData.last_activity_date);

	return online;
};

MessengerCommon.trimText = function(string)
{
	if (BX.type.isString(string))
		return string.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');
	else
		return string;
};

MessengerCommon.getUserIdle = function (userId) {return ''};

MessengerCommon.getUserLastDate = function(userId, userData) {return '';};

MessengerCommon.isIntranet = function() {return true;};

MessengerCommon.isMobileNative = function() {return true;};

MessengerCommon.getCurrentUser = function() {return BX.componentParameters.get('USER_ID', 0);};

MessengerCommon.linesCanVoteAsHead = function() {return false;};

MessengerCommon.linesGetCrmPath = function() {return '';};



/* Dump for BX API */
BX.user = {};

BX.user.getOnlineStatus = function(lastseen, now, utc)
{
	lastseen = BX.type.isDate(lastseen) ? lastseen : (BX.type.isNumber(lastseen) ? new Date(lastseen * 1000) : new Date(0));
	now = BX.type.isDate(now) ? now : (BX.type.isNumber(now) ? new Date(now * 1000) : new Date());
	utc = !!utc;

	var result = {
		'isOnline': false,
		'status': 'offline',
		'statusText': BX.message('U_STATUS_OFFLINE'),
		'lastSeen': lastseen,
		'lastSeenText': '',
		'now': now,
		'utc': utc
	};

	if (lastseen.getTime() === 0)
	{
		return result;
	}

	result.isOnline = now.getTime() - lastseen.getTime() <= parseInt(BX.message('LIMIT_ONLINE'))*1000;
	result.status = result.isOnline? 'online': 'offline';
	result.statusText = BX.message('U_STATUS_'+result.status.toUpperCase());

	if (lastseen.getTime() > 0 && now.getTime() - lastseen.getTime() > 300*1000)
	{
		result.lastSeenText = BX.date.formatLastActivityDate(lastseen, now, utc);
	}

	return result;
};

BX.user.getSecondsForLimitOnline = function()
{
	return parseInt(BX.message.LIMIT_ONLINE);
};

BX.message.LIMIT_ONLINE = BX.componentParameters.get('LIMIT_ONLINE', 1380);

BX.date = {};

BX.date.formatLastActivityDate = function (lastseen, now, utc) {return '';}



/* Database API */
var tables = {
	recent : {
		name : "recent",
		fields : [{name : "id", unique : true}, "value"]
	},
	lastSearch : {
		name : "lastSearch",
		fields : [{name : "id", unique : true}, "value"]
	},
	colleaguesList : {
		name : "colleaguesList",
		fields : [{name : "id", unique : true}, "value"]
	},
};

var TableEntry = function(name, db)
{
	this.name = name;
	this.db = db;
};

TableEntry.prototype = {
	__proto__ : TableEntry.prototype,
	name : null,
	db : null,
	delete : function(filter)
	{
		return this.db.deleteRows({
			tableName : this.name,
			filter : filter
		})
	},
	get : function(filter)
	{
		let getPromise = new BX.Promise();
		this.db.getRows({
			tableName : this.name,
			filter : filter
		}).then(data => getPromise.fulfill(data.result.items, data)
		).catch(e => getPromise.reject(e));

		return getPromise;
	},
	getLike : function(filter)
	{
		let getPromise = new BX.Promise();
		let where = "";
		let fields = Object.keys(filter);
		fields.forEach((key) =>
		{
			let expression = key + " LIKE ?";
			where += (where === ""? "": " AND ") + expression;
		});

		this.db.query({
			query : ("SELECT * FROM " + this.name + " WHERE " + where).toUpperCase(),
			values : Object.values(filter)
		}).then(data => getPromise.fulfill(data.result.items, data)
		).catch(e => getPromise.reject(e));

		return getPromise;
	},
	add : function(insertFields)
	{
		return this.db.addRow({
			tableName : this.name,
			insertFields : insertFields
		})
	}
};

var ReactDatabase = function(dbName, dbUser, dbLanguage)
{
	dbLanguage = dbLanguage? '_'+dbLanguage.toString().toLowerCase(): '';
	dbUser = dbUser? '_'+dbUser.toString().toLowerCase(): '';

	let id = currentDomain.replace(/(http.?:\/\/)|(:|\.)/mg, "_");
	let databaseName = dbName + '_' + id + dbUser + dbLanguage+'.db';
	this.db = BX.dataBase.create({name : databaseName, location : 'default'});
	this.debug = false;

	console.info("ReactDatabase: init "+ databaseName, this.db);
};

ReactDatabase.prototype = {
	__proto__ : ReactDatabase.prototype,
	table : function(desc)
	{
		let tablePromise = new BX.Promise();
		this.db.isTableExists(desc.name)
			.then(() =>
			{
				if (this.debug) console.info("ReactDatabase.table: table '" + desc.name + "' is exists");
				tablePromise.fulfill(new TableEntry(desc.name, this.db))
			})
			.catch((e) =>
				{
					if (this.debug) console.info("ReactDatabase.table: creating table " + desc.name, e);
					this.db.createTable(
						{
							tableName : desc.name,
							fields : desc.fields
						})
						.then(() => tablePromise.fulfill(new TableEntry(desc.name, this.db)))
						.catch((e) => tablePromise.reject(e))
				}
			);

		return tablePromise;
	},
	tableGet : function(desc, filter)
	{
		let tableGetPromise = BX.Promise();
		this.table(desc)
			.then(table => table.get(filter)
				.then(data => tableGetPromise.fulfill(data))
				.catch(e => tableGetPromise.reject(e))
			)
			.catch(e => tableGetPromise.reject(e));

		return tableGetPromise;
	},
	tableClear : function(desc)
	{
		this.table(desc).then(table => table.delete());
	}

};



/* Other API */
if (typeof Object.values === "undefined")
{
	Object.values = function(obj)
	{
		let arr = [];
		for (let key in obj)
		{
			if(obj.hasOwnProperty(key))
			{
				arr.push(obj[key]);
			}
		}

		return arr;
	};
}



/* Initialization */
RecentList.init();