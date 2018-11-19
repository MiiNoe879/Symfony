BX.namespace('BX.Crm.Automation');

if (!BX.Crm.Automation.Runtime) BX.Crm.Automation = (function(BX)
{
	'use strict';

	var Component = function(baseNode)
	{
		if (!BX.type.isDomNode(baseNode))
			throw 'baseNode must be Dom Node Element';

		this.node = baseNode;
	};

	Component.ViewMode = {
		View : 1,
		Edit: 2
	};

	Component.LogStatus = {
		Waiting : 0,
		Running: 1,
		Completed: 2,
		AutoCompleted: 3
	};

	Component.idIncrement = 0;
	Component.generateUniqueId = function()
	{
		++Component.idIncrement;
		return 'crm-automation-cmp-' + Component.idIncrement;
	};

	Component.prototype =
	{
		init: function(data, viewMode)
		{
			var me = this;

			this.viewMode = viewMode || Component.ViewMode.View;

			if (typeof data === 'undefined')
				data = {};

			this.data = data;
			this.initData();

			this.initTracker();
			this.initTriggerManager();
			this.initTemplateManager();
			this.initButtons();
			this.initButtonsPosition();
			this.initHelpTips();
			this.setTitle();
			this.fixTitleColors();

			window.onbeforeunload = function()
			{
				if (me.templateManager.needSave() || me.triggerManager.needSave())
				{
					return BX.message('CRM_AUTOMATION_CMP_NEED_SAVE');
				}
			};

			BX.addCustomEvent('CrmProgressControlAfterSaveSucces', this.onStatusChange.bind(this));
			BX.addCustomEvent('Crm.EntityProgress.Change', this.onEntityProgressChange.bind(this));
		},
		initData: function()
		{
			this.entityTypeId = this.data.ENTITY_TYPE_ID;
			this.entityId = this.data.ENTITY_ID;
			this.entityCategoryId = this.data.ENTITY_CATEGORY_ID;
			this.bizprocEditorUrl = this.data.BIZPROC_EDITOR_URL;
			this.entityStatuses = this.data.ENTITY_STATUSES;
			this.statusesSort = [];
			for(var i = 0; i < this.entityStatuses.length; ++i)
			{
				this.statusesSort.push(this.entityStatuses[i]['STATUS_ID']);
			}
			this.setEntityStatus(this.data.ENTITY_STATUS);

			this.userOptions = {};
			if (BX.type.isPlainObject(this.data.USER_OPTIONS))
			{
				this.userOptions = this.data.USER_OPTIONS;
			}
			this.frameMode = BX.type.isBoolean(this.data.FRAME_MODE) ? this.data.FRAME_MODE : false;
		},
		setEntityStatus: function(status)
		{
			this.entityStatus = status;
			this.currentStatusIndex = -1;

			for(var i = 0; i < this.statusesSort.length; ++i)
			{
				if (this.statusesSort[i] == status)
				{
					this.currentStatusIndex = i;
					break;
				}
			}

			return this;
		},
		isPreviousEntityStatus: function(needle)
		{
			var needleIndex = 0;
			for (var i = 0; i < this.statusesSort.length; ++i)
			{
				if (needle == this.statusesSort[i])
					needleIndex = i;
			}
			return this.currentStatusIndex > -1 && needleIndex < this.currentStatusIndex;
		},
		isCurrentEntityStatus: function(needle)
		{
			return needle == this.entityStatus;
		},
		isNextEntityStatus: function(needle)
		{
			var needleIndex = 0;
			for (var i = 0; i < this.statusesSort.length; ++i)
			{
				if (needle == this.statusesSort[i])
					needleIndex = i;
			}
			return this.currentStatusIndex > -1 && needleIndex > this.currentStatusIndex;
		},
		initTriggerManager: function()
		{
			this.triggerManager = new TriggerManager(this);
			this.triggerManager.init(this.data, this.viewMode);
		},
		reInitTriggerManager: function(triggers)
		{
			if (BX.type.isArray(triggers))
				this.data.TRIGGERS = triggers;
			this.triggerManager.reInit(this.data, this.viewMode);
		},
		initTemplateManager: function()
		{
			this.templateManager = new TemplateManager(this);
			this.templateManager.init(this.data, this.viewMode);
		},
		reInitTemplateManager: function(templates)
		{
			if (BX.type.isArray(templates))
				this.data.TEMPLATES = templates;
			this.templateManager.reInit(this.data, this.viewMode);
		},
		initButtons: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');

			if (buttonsNode)
			{
				if (this.viewMode === Component.ViewMode.View)
				{
					BX.hide(buttonsNode);
				}
			}
			this.bindSaveButton();
			this.bindCancelButton();
			this.bindChangeViewButton();
		},
		initButtonsPosition: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');
			var pinButton = BX.create("span", {
				attrs: { className: "crm-lead-header-contact-btn crm-automation-pin-btn crm-lead-header-contact-btn-pin" },
				events: {
					click: function()
					{
						this.classList.toggle("crm-lead-header-contact-btn-unpin");
						buttonsNode.classList.toggle("crm-automation-buttons-fixed");
					}
				}
			});

			if (buttonsNode)
			{
				buttonsNode.appendChild(pinButton);
				if (this.frameMode)
				{
					BX.addClass(buttonsNode, 'crm-automation-buttons-fixed-slider');
				}
			}
		},
		initHelpTips: function()
		{
			var tipsNodes = this.node.querySelectorAll('[data-role="automation-help-tips"]');

			for (var i = 0; i < tipsNodes.length; ++i)
			{
				HelpHint.bindToNode(tipsNodes[i]);
			}
		},
		reInitButtons: function()
		{
			var buttonsNode = this.node.querySelector('[data-role="automation-buttons"]');
			if (buttonsNode && this.viewMode === Component.ViewMode.View)
			{
				BX.hide(buttonsNode);
			}
			else if (buttonsNode && this.viewMode === Component.ViewMode.Edit)
			{
				BX.show(buttonsNode);
			}

			var changeViewBtn = this.node.querySelector('[data-role="automation-btn-change-view"]');
			if (changeViewBtn)
			{
				changeViewBtn.innerHTML = changeViewBtn.getAttribute('data-label-'
					+(this.viewMode === Component.ViewMode.View ? 'edit' : 'view'));
			}
		},
		setTitle: function()
		{
			var titleNode = this.node.querySelector('[data-role="automation-title"]');
			if (titleNode)
			{
				titleNode.innerHTML = titleNode.getAttribute('data-title-'
					+(this.viewMode === Component.ViewMode.View ? 'view' : 'edit')
				);
			}
		},
		fixTitleColors: function()
		{
			var i, bgcolor, titles = this.node.querySelectorAll('[data-role="automation-status-title"]');
			for (i = 0; i < titles.length; ++i)
			{
				bgcolor = titles[i].getAttribute('data-bgcolor');
				if (bgcolor)
				{
					var bigint = parseInt(bgcolor, 16);
					var r = (bigint >> 16) & 255;
					var g = (bigint >> 8) & 255;
					var b = bigint & 255;
					var y = 0.21 * r + 0.72 * g + 0.07 * b;

					if (y < 145) // dark background
					{
						titles[i].style.color =  'white';
					}
				}
			}
		},
		initTracker: function()
		{
			this.tracker = new Tracker(this);
			this.tracker.init(this.data.LOG);
		},
		bindSaveButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-save"]');

			if (button)
			{
				BX.bind(button, 'click', function(e)
				{
					e.preventDefault();
					me.saveAutomation();
				});
			}
		},
		bindCancelButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-cancel"]');

			if (button)
			{
				BX.bind(button, 'click', function(e)
				{
					e.preventDefault();
					me.changeViewMode(Component.ViewMode.View, true);
				});
			}
		},
		bindChangeViewButton: function()
		{
			var me = this, button = this.node.querySelector('[data-role="automation-btn-change-view"]');

			if (button)
			{
				button.innerHTML = button.getAttribute('data-label-'
						+(this.viewMode === Component.ViewMode.View ? 'edit' : 'view'));

				BX.bind(button, 'click', function(e)
				{
					e.preventDefault();
					var viewMode = me.viewMode === Component.ViewMode.Edit?
						Component.ViewMode.View : Component.ViewMode.Edit;

					me.changeViewMode(viewMode);
				});
			}
		},
		getAjaxUrl: function()
		{
			return  BX.util.add_url_param(this.data.AJAX_URL, {
				site_id: BX.message('SITE_ID'),
				sessid: BX.thurly_sessid()
			});
		},
		saveAutomation: function(callback)
		{
			var me = this, data = {
				ajax_action: 'save_automation',
				entity_type_id: this.entityTypeId,
				triggers: this.triggerManager.serialize(),
				templates: this.templateManager.serialize()
			};

			return BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: data,
				onsuccess: function(response)
				{
					if (response.SUCCESS)
					{
						me.reInitTemplateManager(response.DATA.templates);
						me.reInitTriggerManager(response.DATA.triggers);
						me.changeViewMode(Component.ViewMode.View);
						if (callback)
						{
							callback(response.DATA)
						}
					}
					else
						alert(response.ERRORS[0]);
				}
			});
		},
		changeViewMode: function(mode, silent)
		{
			if (!silent && (this.templateManager.needSave() || this.triggerManager.needSave()))
			{
				alert(BX.message('CRM_AUTOMATION_CMP_NEED_SAVE'));
				return;
			}

			if (mode !== Component.ViewMode.View && mode !== Component.ViewMode.Edit)
				throw 'Unknown view mode';

			this.viewMode = mode;

			this.reInitTriggerManager();
			this.reInitTemplateManager();
			this.reInitButtons();
			this.setTitle();
		},
		canEdit: function()
		{
			return this.data['CAN_EDIT'];
		},
		onStatusChange: function(progressControl, data)
		{
			if (data && data['VALUE'])
			{
				this.setEntityStatus(data['VALUE']);
				this.updateTracker();
			}
		},
		onEntityProgressChange: function(progressControl, data)
		{
			if (
				data.entityTypeId === this.entityTypeId
				&& data.entityId === this.entityId
				&& data.currentStepId
			)
			{

				this.setEntityStatus(data.currentStepId);
				//need to wait BX.Crm.EntityDetailProgressControl.save()
				setTimeout(this.updateTracker.bind(this), 300);
			}
		},
		updateTracker: function()
		{
			var me = this;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.getAjaxUrl(),
				data: {
					ajax_action: 'get_log',
					entity_type_id: this.entityTypeId,
					entity_id: this.entityId
				},
				onsuccess: function (response)
				{
					if (response.DATA && response.DATA.LOG)
					{
						me.tracker.reInit(response.DATA.LOG);
						if (me.viewMode === Component.ViewMode.View)
						{
							me.templateManager.reInit();
						}
					}
				}
			});
		},
		onGlobalHelpClick: function(e)
		{
			e.preventDefault();
			if (this.data['B24_TARIF_ZONE'] === 'en')
			{
				window.open('http://helpdesk.thurlyos.com/?url=open/4781101/');
			}
			else if (this.data['B24_TARIF_ZONE'] === 'de')
			{
				window.open('https://helpdesk.thurlyos.de/?url=open/4781105/');
			}
			else if (this.data['B24_TARIF_ZONE'] === 'es')
			{
				window.open('https://helpdesk.thurlyos.es/?url=open/5859003/');
			}
			else if (BX.Helper && BX.Helper.frameOpenUrl.indexOf('//helpdesk.thurlyos.ru/') > 0)
			{
				BX.Helper.show("redirect=detail&HD_ID=5265469");
			}
			else
			{
				window.open('https://helpdesk.thurlyos.ru/?url=open/5265469/');
			}
		},
		getUserOption: function(category, key, defaultValue)
		{
			var result = defaultValue;

			if (this.userOptions[category] && this.userOptions[category][key])
			{
				result = this.userOptions[category][key];
			}
			return result;
		},
		setUserOption: function(category, key, value)
		{
			if (!BX.type.isPlainObject(this.userOptions[category]))
			{
				this.userOptions[category] = {};
			}
			var storedValue = this.userOptions[category][key];

			if (storedValue !== value)
			{
				this.userOptions[category][key] = value;
				BX.userOptions.save(
					'crm.automation',
					category,
					key,
					value,
					false
				);
			}
			return this;
		}
	};

	var TemplateManager = function(component)
	{
		this.component = component;
	};

	TemplateManager.prototype =
	{
		init: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			this.availableRobots = BX.type.isArray(data.AVAILABLE_ROBOTS) ? data.AVAILABLE_ROBOTS : [];
			this.availableRobotsMap = {};
			for (var i = 0; i < this.availableRobots.length; ++i)
			{
				this.availableRobotsMap[this.availableRobots[i]['CLASS']] = this.availableRobots[i];
			}

			this.templatesData = BX.type.isArray(data.TEMPLATES) ? data.TEMPLATES : [];

			this.initTemplates();
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			if (BX.type.isArray(data.TEMPLATES))
				this.templatesData = data.TEMPLATES;

			this.reInitTemplates(this.templatesData);
		},
		initTemplates: function()
		{
			this.templates = [];
			this.templatesMap = {};

			for (var i = 0; i < this.templatesData.length; ++i)
			{
				var tpl = new Template(this);
				tpl.init(this.templatesData[i], this.viewMode);

				this.templates.push(tpl);
				this.templatesMap[tpl.getStatusId()] = tpl;
			}
		},
		reInitTemplates: function(templates)
		{
			for (var i = 0; i < this.templates.length; ++i)
			{
				if (templates[i])
				{
					this.templates[i].reInit(templates[i], this.viewMode);
				}
			}
		},
		getAvailableRobots: function()
		{
			return this.availableRobots;
		},
		getRobotDescription: function(type)
		{
			return this.availableRobotsMap[type] || null;
		},
		serialize: function()
		{
			var templates = [];

			for (var i = 0; i < this.templates.length; ++i)
			{
				templates.push(this.templates[i].serialize());
			}

			return templates;
		},
		getTemplateByColumnNode: function(node)
		{
			var statusId = node.getAttribute('data-status-id');
			return this.getTemplateByStatusId(statusId);
		},
		getTemplateByStatusId: function(statusId)
		{
			return this.templatesMap[statusId] || null;
		},
		needSave: function()
		{
			var modified = false;
			for (var i = 0; i < this.templates.length; ++i)
			{
				if (this.templates[i].isModified())
				{
					modified = true;
					break;
				}
			}
			return modified;
		}
	};

	var Template = function(manager)
	{
		this.manager = manager;
		this.component = manager.component;
		this.data = {};
	};

	Template.prototype =
	{
		init: function(data, viewMode)
		{
			if (BX.type.isPlainObject(data))
				this.data = data;

			this.viewMode = viewMode || Component.ViewMode.View;
			this.node = this.component.node.querySelector('[data-role="automation-template"][data-status-id="'+this.getStatusId()+'"]');
			this.listNode = this.node.querySelector('[data-role="robot-list"]');
			this.buttonsNode = this.node.querySelector('[data-role="buttons"]');
			this.initRobots();
			this.initButtons();

			this.modified = false;

			if (!this.isExternalModified())
			{
				//register DD
				jsDD.registerDest(this.node, 10);
			}
		},
		reInit: function(data, viewMode)
		{
			BX.cleanNode(this.listNode);
			BX.cleanNode(this.buttonsNode);

			this.init(data, viewMode)
		},
		initRobots: function()
		{
			this.robots = [];
			this.robotsMap = {};
			if (BX.type.isArray(this.data.ROBOTS))
			{
				for (var i = 0; i < this.data.ROBOTS.length; ++i)
				{
					var robot = new Robot(this);
					robot.init(this.data.ROBOTS[i], this.viewMode);
					this.insertRobotNode(robot.node);
					this.robots.push(robot);
					this.robotsMap[robot.getId()] = robot;
				}
			}
		},
		getStatusId: function()
		{
			return this.data.ENTITY_STATUS;
		},
		getTemplateId: function()
		{
			var id = parseInt(this.data.TEMPLATE_ID);
			return !isNaN(id) ? id : 0;
		},
		initButtons: function()
		{
			if (this.isExternalModified())
			{
				this.createExternalLocker();
			}
			else if (this.viewMode === Component.ViewMode.Edit)
			{
				if (!this.isExternalModified())
					this.createAddButton();

				if (this.getTemplateId() > 0)
					this.createExternalEditTemplateButton();
			}

			if (this.viewMode === Component.ViewMode.View && this.component.canEdit())
			{
				this.createEditButton();
			}
		},
		createAddButton: function()
		{
			var me = this,
				anchor = BX.create('a', {
							text: BX.message('CRM_AUTOMATION_CMP_ADD'),
							props: {
								href: '#'
							},
							events: {
								click: function(e)
								{
									e.preventDefault();
									me.onAddButtonClick(this);
								}
							},
							attrs:{
								className: 'crm-automation-robot-btn-add'
							}

						});

			this.buttonsNode.appendChild(anchor);
		},
		createEditButton: function()
		{
			var me = this,
				anchor = BX.create('a', {
					text: BX.message('CRM_AUTOMATION_CMP_AUTOMATION_EDIT'),
					props: {
						href: '#'
					},
					events: {
						click: function(e)
						{
							e.preventDefault();
							me.manager.component.changeViewMode(Component.ViewMode.Edit);
						}
					},
					attrs: { className: "crm-automation-robot-btn-set" }
				});
			this.buttonsNode.appendChild(anchor);
		},
		createExternalEditTemplateButton: function()
		{
			var me = this,
				anchor = BX.create('a', {
				text: BX.message('CRM_AUTOMATION_CMP_EXTERNAL_EDIT'),
				props: {
					href: '#'
				},
				events: {
					click: function(e)
					{
						e.preventDefault();
						me.onExternalEditTemplateButtonClick(this);
					}
				},
				attrs: { className: "crm-automation-robot-btn-set" }
			});

			if (!this.manager.component.bizprocEditorUrl.length)
			{
				BX.addClass(anchor, 'crm-automation-robot-btn-set-locked');
			}

			this.buttonsNode.appendChild(anchor);
		},
		createExternalLocker: function()
		{
			var me = this, div = BX.create("div", {
				attrs: {
					className: "crm-automation-robot-container"
				},
				children: [
					BX.create('div', {
						attrs: {
							className: 'crm-automation-robot-container-wrapper crm-automation-robot-container-wrapper-lock'
						},
						children: [
							BX.create("div", {
								attrs: { className: "crm-automation-robot-deadline" }
							}),
							BX.create("div", {
								attrs: { className: "crm-automation-robot-title" },
								text: BX.message('CRM_AUTOMATION_CMP_EXTERNAL_EDIT_TEXT')
							})
						]
					})
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				var settingsBtn = BX.create('div', {
					attrs: {
						className: 'crm-automation-robot-btn-settings'
					},
					text: BX.message('CRM_AUTOMATION_CMP_EDIT')
				});
				BX.bind(div, 'click', function(e)
				{
					me.onExternalEditTemplateButtonClick(this);
				});
				div.appendChild(settingsBtn);
				BX.addClass(div.firstChild, 'crm-automation-robot-container-wrapper-border');
				var deleteBtn = BX.create('SPAN', {
					attrs: {
						className: 'crm-automation-robot-btn-delete'
					}
				});
				BX.bind(deleteBtn, 'click', function(e)
				{
					me.onUnsetExternalModifiedClick(this);
				});
				div.lastChild.appendChild(deleteBtn);
			}

			this.listNode.appendChild(div);
		},
		onAddButtonClick: function(button)
		{
			var me = this, i, j, menuItems = {employee: [], client: [], ads: [], other: []};

			var title, settings, categories, availableRobots = this.manager.getAvailableRobots();
			var menuItemClickHandler = function(e, item)
			{
				if (!item.isDemo)
				{
					var robotData = BX.clone(item.robotData);

					if (
						robotData['ROBOT_SETTINGS']
						&& robotData['ROBOT_SETTINGS']['TITLE_CATEGORY']
						&& robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category]
					)
					{
						robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE_CATEGORY'][item.category];
					}
					else if (robotData['ROBOT_SETTINGS'] && robotData['ROBOT_SETTINGS']['TITLE'])
					{
						robotData['NAME'] = robotData['ROBOT_SETTINGS']['TITLE'];
					}

					me.addRobot(robotData, function(robot)
					{
						me.openRobotSettingsDialog(robot, {ADD_MENU_CATEGORY: item.category});
					});
				}
			};

			for (i = 0; i < availableRobots.length; ++i)
			{
				if (availableRobots[i]['EXCLUDED'])
					continue;
				settings = BX.type.isPlainObject(availableRobots[i]['ROBOT_SETTINGS'])
					? availableRobots[i]['ROBOT_SETTINGS'] : {};

				title = availableRobots[i].NAME;
				if (settings['TITLE'])
					title = settings['TITLE'];

				//TODO: remove after aprooving app
				var isDemo = settings['IS_DEMO'];

				if (window.location.search.indexOf('disable_demo') >= 0)
					isDemo = false;

				if (isDemo)
					title += ' ' + BX.message('CRM_AUTOMATION_CMP_IS_DEMO');

				categories = [];
				if (settings['CATEGORY'])
				{
					categories = BX.type.isArray(settings['CATEGORY']) ? settings['CATEGORY'] : [settings['CATEGORY']];
				}

				if (!categories.length)
				{
					categories.push('other');
				}

				for (j = 0; j < categories.length; ++j)
				{
					if (!menuItems[categories[j]])
						continue;

					menuItems[categories[j]].push({
						text: title,
						robotData: availableRobots[i],
						isDemo: isDemo,
						category: categories[j],
						className: isDemo ? 'crm-automation-menu-item-disabled menu-popup-no-icon' : '',
						onclick: menuItemClickHandler
					});
				}
			}

			if (menuItems['other'].length > 0)
			{
				menuItems['other'].push({delimiter: true});
			}

			menuItems['other'].push({
				text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE'),
				href: '/marketplace/category/crm_bots/',
				target: '_blank'
			});

			var menuId = button.getAttribute('data-menu-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				button.setAttribute('data-menu-id', menuId);
			}

			BX.PopupMenu.show(
				menuId,
				button,
				[
					{
						text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_EMPLOYEE'),
						items: menuItems['employee']
					},
					{
						text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_CLIENT'),
						items: menuItems['client']
					},
					{
						text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_ADS'),
						items: menuItems['ads']
					},
					{
						text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_OTHER'),
						items: menuItems['other']
					}
				],
				{
					autoHide: true,
					offsetLeft: (BX.pos(button)['width'] / 2),
					angle: { position: 'top', offset: 0 }
				}
			);
		},
		onExternalEditTemplateButtonClick: function(button)
		{
			if (!this.manager.component.bizprocEditorUrl.length)
			{
				if (BX.getClass('B24.licenseInfoPopup'))
				{
					B24.licenseInfoPopup.show(
						'crm_automation_designer',
						BX.message('CRM_AUTOMATION_CMP_EXTERNAL_EDIT'),
						BX.message('CRM_AUTOMATION_CMP_EXTERNAL_EDIT_LOCKED')
					);
				}
				return;
			}

			var templateId = this.getTemplateId();
			if (templateId > 0)
				this.openBizprocEditor(templateId);
		},
		onUnsetExternalModifiedClick: function(button)
		{
			this.data['IS_EXTERNAL_MODIFIED'] = false;
			this.data['UNSET_EXTERNAL_MODIFIED'] = true;
			this.reInit(null, this.viewMode);
		},
		openBizprocEditor: function(templateId)
		{
			var url = this.manager.component.bizprocEditorUrl.replace('#ID#', templateId);
			top.window.location.href = url;
		},
		addRobot: function(robotData, callback)
		{
			var robot = new Robot(this);
			var initData = {
				Type: robotData['CLASS'],
				Properties: {
					Title: robotData['NAME']
				}
			};

			if (this.robots.length > 0)
			{
				var parentRobot = this.robots[this.robots.length - 1];
				if (!parentRobot.delay.isNow() || parentRobot.isExecuteAfterPrevious())
				{
					initData['Delay'] = parentRobot.delay.serialize();
					initData['ExecuteAfterPrevious'] =  1;
				}
			}

			robot.init(initData, this.viewMode);
			robot.draft = true;
			if (callback)
				callback(robot);
		},
		insertRobot: function(robot, beforeRobot)
		{
			if (beforeRobot)
			{
				for (var i = 0; i < this.robots.length; ++i)
				{
					if (this.robots[i] !== beforeRobot)
						continue;
					this.robots.splice(i, 0, robot);
					break;
				}
			}
			else
			{
				this.robots.push(robot);
			}
			this.modified = true;
		},
		deleteRobot: function(robot, callback)
		{
			for(var i = 0; i < this.robots.length; ++i)
			{
				if (this.robots[i] === robot)
				{
					this.robots.splice(i, 1);
					break;
				}
			}
			if (callback)
				callback(robot);
			this.modified = true;
		},
		insertRobotNode: function(robotNode, beforeNode)
		{
			if (beforeNode)
			{
				this.listNode.insertBefore(robotNode, beforeNode);
			}
			else
			{
				this.listNode.appendChild(robotNode);
			}
		},
		/**
		 * @param {Robot} robot
		 * @param {Object} [context]
		 */
		openRobotSettingsDialog: function(robot, context)
		{
			if (Runtime.getRobotSettingsDialog())
				return;

			var me = this, formName = 'crm_automation_robot_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				}
			});

			form.appendChild(me.renderDelaySettings(robot));
			form.appendChild(me.renderConditionSettings(robot));

			var iconHelp = BX.create('div', {
				attrs: { className: 'crm-automation-robot-help' },
				events: {click: BX.delegate(this.component.onGlobalHelpClick, this.component)}
			});
			form.appendChild(iconHelp);

			if (!BX.type.isPlainObject(context))
				context = {};

			context['ENTITY_TYPE_ID'] = this.manager.component.entityTypeId;
			context['ENTITY_CATEGORY_ID'] = this.manager.component.entityCategoryId;
			context['TEMPLATE_STATUS'] = this.getStatusId();

			Runtime.setRobotSettingsDialog({
				template: this,
				entityTypeId: this.data.ENTITY_TYPE_ID,
				entityStatus: this.data.ENTITY_STATUS,
				context: context,
				robot: robot,
				sendAjaxRequest: this.getRobotAjaxResponse,
				form: form
			});
			BX.ajax({
				method: 'POST',
				dataType: 'html',
				url: this.manager.component.getAjaxUrl(),
				data: {
					ajax_action: 'get_robot_dialog',
					entity_type_id: this.data.ENTITY_TYPE_ID,
					document_status: this.data.ENTITY_STATUS,
					context: context,
					robot: robot.serialize(),
					form_name: formName
				},
				onsuccess: function(html)
				{
					if (html)
					{
						var dialogRows = BX.create('div', {
							html: html
						});
						form.appendChild(dialogRows);
					}
					me.showRobotSettingsPopup(robot, form);
				}
			});
		},
		getRobotAjaxResponse: function(request, callback)
		{
			if (!BX.type.isPlainObject(request))
				request = {};

			request.ajax_action = 'get_robot_ajax_response';
			request.entity_type_id = this.entityTypeId;
			request.document_status = this.entityStatus;
			request.context = this.context;
			request.robot = this.robot.serialize();

			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.template.manager.component.getAjaxUrl(),
				data: request,
				onsuccess: function(response)
				{
					if (BX.type.isFunction(callback))
						callback(response['DATA'])
				}
			});
		},
		showRobotSettingsPopup: function(robot, form)
		{
			BX.addClass(this.component.node, 'automation-base-blocked');

			this.initRobotSettingsControls(robot, form);

			var popupWidth = parseInt(this.component.getUserOption('defaults', 'robot_settings_popup_width', 580));
			var popupMinWidth = 580;

			if (robot.data.Type === 'CrmSendEmailActivity')
			{
				popupMinWidth += 124;
				if (popupWidth < popupMinWidth)
				{
					popupWidth = popupMinWidth;
				}
			}

			var me = this, popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: robot.getProperty('Title'),
				content: form,
				closeIcon: true,
				width: popupWidth,
				resizable: {
					minWidth: popupMinWidth,
					minHeight: 100
				},
				// zIndex: 9,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: false,
				events: {
					onPopupClose: function(popup)
					{
						me.currentRobot = null;
						Runtime.setRobotSettingsDialog(null);
						me.destroyRobotSettingsControls();
						popup.destroy();
						BX.removeClass(me.component.node, 'automation-base-blocked');
					},
					onPopupResize: function()
					{
						me.onResizeRobotSettings();
					},
					onPopupResizeEnd: function() {
						me.component.setUserOption(
							'defaults',
							'robot_settings_popup_width',
							this.getWidth()
						);
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								me.saveRobotSettings(form, robot, BX.delegate(function()
								{
									this.popupWindow.close()
								}, this));
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			me.currentRobot = robot;
			Runtime.getRobotSettingsDialog().popup = popup;
			popup.show();
		},
		initRobotSettingsControls: function(robot, node)
		{
			if (!BX.type.isArray(this.robotSettingsControls))
				this.robotSettingsControls = [];

			var i, userSelectors = node.querySelectorAll('[data-role="user-selector"]');
			for (i = 0; i < userSelectors.length; ++i)
			{
				this.robotSettingsControls.push(
					new Destination(this.manager.component, userSelectors[i])
				);
			}

			var FileSelectors = node.querySelectorAll('[data-role="file-selector"]');
			for (i = 0; i < FileSelectors.length; ++i)
			{
				this.robotSettingsControls.push(
					new FileSelector(this.manager.component, FileSelectors[i])
				);
			}

			var inlineSelectors = node.querySelectorAll('[data-role="inline-selector-target"]');
			for (i = 0; i < inlineSelectors.length; ++i)
			{
				this.robotSettingsControls.push(
					new InlineSelector(this.manager.component, inlineSelectors[i])
				);
			}

			var inlineHtmlSelectors = node.querySelectorAll('[data-role="inline-selector-html"]');
			for (i = 0; i < inlineHtmlSelectors.length; ++i)
			{
				this.robotSettingsControls.push(
					new InlineSelectorHtml(this.manager.component, inlineHtmlSelectors[i])
				);
			}

			var timeSelectors = node.querySelectorAll('[data-role="time-selector"]');
			for (i = 0; i < timeSelectors.length; ++i)
			{
				this.robotSettingsControls.push(
					new TimeSelector(timeSelectors[i])
				);
			}

			var saveStateCheckboxes = node.querySelectorAll('[data-role="save-state-checkbox"]');
			for (i = 0; i < saveStateCheckboxes.length; ++i)
			{
				this.robotSettingsControls.push(
					new SaveStateCheckbox(saveStateCheckboxes[i], robot)
				);
			}

			var helpTips = node.querySelectorAll('[data-role="automation-help-tip"]');
			for (i = 0; i < helpTips.length; ++i)
			{
				HelpHint.bindToNode(helpTips[i]);
			}
		},
		destroyRobotSettingsControls: function ()
		{
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].destroy))
						this.robotSettingsControls[i].destroy();
				}
			}
			this.robotSettingsControls = null;
		},
		onBeforeSaveRobotSettings: function ()
		{
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].onBeforeSave))
						this.robotSettingsControls[i].onBeforeSave();
				}
			}
		},
		onResizeRobotSettings: function ()
		{
			if (BX.type.isArray(this.robotSettingsControls))
			{
				for (var i = 0; i < this.robotSettingsControls.length; ++i)
				{
					if (BX.type.isFunction(this.robotSettingsControls[i].onPopupResize))
						this.robotSettingsControls[i].onPopupResize();
				}
			}
		},
		/**
		 * @param {Robot} robot
		 */
		renderDelaySettings: function(robot)
		{
			var delay = BX.clone(robot.getDelayInterval());
			var isExecuteAfterPrevious = robot.isExecuteAfterPrevious();

			var idSalt = Component.generateUniqueId();

			var executeAfterPreviousCheckbox = BX.create("input", {
				attrs: {
					type: "checkbox",
					id: "param-group-3-1" + idSalt,
					name: "execute_after_previous",
					value: '1',
					style: 'vertical-align: middle'
				}
			});

			var delayTypeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_type",
					value: delay.type
				}
			});
			var delayValueNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_value",
					value: delay.value
				}
			});
			var delayValueTypeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_value_type",
					value: delay.valueType
				}
			});
			var delayBasisNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_basis",
					value: delay.basis
				}
			});
			var delayWorkTimeNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "delay_worktime",
					value: delay.workTime ? 1 : 0
				}
			});
			if (isExecuteAfterPrevious)
			{
				executeAfterPreviousCheckbox.setAttribute('checked', 'checked');
			}

			var delayIntervalLabelNode = BX.create("span", {
				attrs: {
					className: "crm-automation-popup-settings-link crm-automation-delay-interval-basis"
				}
			});

			var basisFields = [];
			if (BX.type.isArray(this.component.data['ENTITY_FIELDS']))
			{
				var i, field;
				for (i = 0; i < this.component.data['ENTITY_FIELDS'].length; ++i)
				{
					field = this.component.data['ENTITY_FIELDS'][i];
					if (field['Type'] == 'date' || field['Type'] == 'datetime' || field['Type'] == 'UF:date')
						basisFields.push(field);
				}
			}

			var delayIntervalSelector = new DelayIntervalSelector({
				labelNode: delayIntervalLabelNode,
				typeNode: delayTypeNode,
				valueNode: delayValueNode,
				valueTypeNode: delayValueTypeNode,
				basisNode: delayBasisNode,
				basisFields: basisFields,
				workTimeNode: delayWorkTimeNode
			});

			var div = BX.create("div", {
				attrs: { className: "crm-automation-popup-settings crm-automation-popup-settings-flex" },
				children: [
					BX.create("div", {
						attrs: { className: "crm-automation-popup-settings-block crm-automation-popup-settings-block-flex" },
						children: [
							BX.create("span", {
								attrs: { className: "crm-automation-popup-settings-title-wrapper" },
								children: [
									delayTypeNode,
									delayValueNode,
									delayValueTypeNode,
									delayBasisNode,
									delayWorkTimeNode,
									BX.create("span", {
										attrs: { className: "crm-automation-popup-settings-title crm-automation-popup-settings-title-left" },
										text: BX.message('CRM_AUTOMATION_CMP_TO_EXECUTE') + ":"
									}),
									delayIntervalLabelNode
								]
							})
						]
					}),
					BX.create("div", {
						attrs: { className: "crm-automation-popup-settings-block" },
						children: [
							executeAfterPreviousCheckbox,
							BX.create("label", {
								attrs: {
									for: "param-group-3-1" + idSalt,
									style: 'color: #535C69'
								},
								text: BX.message('CRM_AUTOMATION_CMP_AFTER_PREVIOUS_WIDE')
							})
						]
					})
				]
			});

			delayIntervalSelector.init(delay);

			return div;
		},
		/**
		 * @param {Object} formFields
		 * @param {Robot} robot
		 * @returns {*}
		 */
		setDelaySettingsFromForm: function(formFields,  robot)
		{
			var delay = new DelayInterval();
			delay.setType(formFields['delay_type']);
			delay.setValue(formFields['delay_value']);
			delay.setValueType(formFields['delay_value_type']);
			delay.setBasis(formFields['delay_basis']);
			delay.setWorkTime(formFields['delay_worktime'] === '1');

			var executeAfterPrevious = (formFields['execute_after_previous'] && (formFields['execute_after_previous']) === '1');

			robot.setDelayInterval(delay);
			robot.setExecuteAfterPrevious(executeAfterPrevious);

			return this;
		},
		/**
		 * @param {Robot} robot
		 */
		renderConditionSettings: function(robot)
		{
			/** @var {Condition} condition */
			var condition = BX.clone(robot.getCondition());

			var conditionFieldNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "condition_field",
					value: condition.field
				}
			});
			var conditionConditionNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "condition_condition",
					value: condition.condition
				}
			});
			var conditionValueNode = BX.create("input", {
				attrs: {
					type: "hidden",
					name: "condition_value",
					value: condition.value
				}
			});

			var labelNode = BX.create("span", {
				attrs: {
					className: "crm-automation-popup-settings-link-wrapper"
				}
			});

			var conditionSelector = new ConditionSelector({
				labelNode: labelNode,
				fieldNode: conditionFieldNode,
				conditionNode: conditionConditionNode,
				valueNode: conditionValueNode,
				fields: this.component.data['ENTITY_FIELDS']
			});

			var div = BX.create("div", {
				attrs: { className: "crm-automation-popup-settings" },
				children: [
					BX.create("div", {
						attrs: { className: "crm-automation-popup-settings-block" },
						children: [
							conditionFieldNode,
							conditionConditionNode,
							conditionValueNode,
							BX.create("span", {
								attrs: { className: "crm-automation-popup-settings-title" },
								text: BX.message('CRM_AUTOMATION_ROBOT_CONDITION') + ":"
							}),
							labelNode
						]
					})
				]
			});

			conditionSelector.init(condition);
			return div;
		},
		/**
		 * @param {Object} formFields
		 * @param {Robot} robot
		 * @returns {*}
		 */
		setConditionSettingsFromForm: function(formFields,  robot)
		{
			var condition = new Condition();
			if (formFields['condition_field'])
			{
				condition.setField(formFields['condition_field']);
				condition.setCondition(formFields['condition_condition']);
				condition.setValue(formFields['condition_value']);
			}
			robot.setCondition(condition);
			return this;
		},

		saveRobotSettings: function(form, robot, callback)
		{
			this.onBeforeSaveRobotSettings();
			var me = this, formData = BX.ajax.prepareForm(form);

			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: this.manager.component.getAjaxUrl(),
				data: {
					ajax_action: 'save_robot_settings',
					entity_type_id: this.data.ENTITY_TYPE_ID,
					robot: robot.serialize(),
					form_data: formData['data']
				},
				onsuccess: function(response)
				{
					if (response.SUCCESS)
					{
						robot.updateData(response.DATA.robot);
						me.setDelaySettingsFromForm(formData['data'], robot);
						me.setConditionSettingsFromForm(formData['data'], robot);

						if (robot.draft)
						{
							me.robots.push(robot);
							me.insertRobotNode(robot.node)
						}
						delete robot.draft;

						robot.reInit();
						me.modified = true;
						if (callback)
						{
							callback(response.DATA)
						}
					}
					else
						alert(response.ERRORS[0]);
				}
			});
		},
		serialize: function()
		{
			var data = this.data;
			data['ROBOTS'] = [];

			for (var i = 0; i < this.robots.length; ++i)
			{
				data['ROBOTS'].push(this.robots[i].serialize());
			}

			return data;
		},
		isExternalModified: function()
		{
			return (this.data['IS_EXTERNAL_MODIFIED'] === true);
		},
		getRobotById: function(id)
		{
			return this.robotsMap[id] || null;
		},
		isModified: function()
		{
			return this.modified;
		}
	};

	var Robot = function(template)
	{
		this.template = template;
		this.templateManager = template.manager;
		this.component = this.templateManager.component;
		this.tracker = template.manager.component.tracker;
	};

	Robot.generateName = function()
	{
		return 'A' + parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000)
			+ '_'+parseInt(Math.random()*100000);
	};

	Robot.prototype =
	{
		init: function(data, viewMode)
		{
			if (data)
				this.data = data;
			if (!this.data.Name)
			{
				this.data.Name = Robot.generateName();
			}

			this.delay = new DelayInterval(this.data.Delay);
			this.condition = new Condition(this.data.Condition);
			this.viewMode = viewMode || Component.ViewMode.View;
			this.node = this.createNode();
		},
		reInit: function(data, viewMode)
		{
			var node = this.node;
			this.node = this.createNode();
			if (node.parentNode)
				node.parentNode.replaceChild(this.node, node);
		},
		getProperty: function(name)
		{
			return this.data.Properties[name] || null;
		},
		setProperty: function(name, value)
		{
			this.data.Properties[name] = value;
			return this;
		},
		getId: function()
		{
			return this.data.Name || null;
		},
		getLogStatus: function()
		{
			var status = Component.LogStatus.Waiting;
			var log = this.tracker.getRobotLog(this.getId());
			if (log)
			{
				status = parseInt(log['STATUS']);
			}
			else if (this.data.DelayName)
			{
				//If delay was executed, we can set Running status to parent robot.
				log = this.tracker.getRobotLog(this.data.DelayName);
				if (log && parseInt(log['STATUS']) > Component.LogStatus.Waiting)
					status = Component.LogStatus.Running;
			}

			return status;
		},
		getLogErrors: function()
		{
			var errors = [], log = this.tracker.getRobotLog(this.getId());
			if (log && log.ERRORS)
			{
				errors = log.ERRORS;
			}

			return errors;
		},
		createNode: function()
		{
			var me = this, status = this.getLogStatus(), loader;

			var settings = this.getDescriptionSettings();

			var wrapperClass = 'crm-automation-robot-container-wrapper';
			if (this.viewMode === Component.ViewMode.Edit)
			{
				wrapperClass += ' crm-automation-robot-container-wrapper-draggable';
			}

			var targetLabel = BX.message('CRM_AUTOMATION_CMP_TO');
			var targetNode = BX.create("a", {
				attrs: {
					className: "crm-automation-robot-settings-name",
					title: BX.message('CRM_AUTOMATION_CMP_AUTOMATICALLY')
				}
			});

			if (settings['IS_AUTO'])
			{
				targetNode.textContent = BX.message('CRM_AUTOMATION_CMP_AUTOMATICALLY');
			}
			else if (BX.type.isPlainObject(this.data.viewData))
			{
				var labelText = this.data.viewData.responsibleLabel
					.replace('{=Document:ASSIGNED_BY_ID}', BX.message('CRM_AUTOMATION_CMP_RESPONSIBLE'))
					.replace('author', BX.message('CRM_AUTOMATION_CMP_RESPONSIBLE'));

				if (labelText.indexOf('{=Document') >= 0 && BX.type.isArray(this.component.data['ENTITY_FIELDS']))
				{
					var i, field;
					for (i = 0; i < this.component.data['ENTITY_FIELDS'].length; ++i)
					{
						field = this.component.data['ENTITY_FIELDS'][i];
						labelText = labelText.replace(field['SystemExpression'], field['Name']);
					}
				}

				targetNode.textContent = labelText;
				targetNode.setAttribute('title', labelText);

				if (this.data.viewData.responsibleUrl)
				{
					targetNode.href = this.data.viewData.responsibleUrl;
					if (this.component.frameMode)
					{
						targetNode.setAttribute('target', '_blank');
					}
				}

				if (parseInt(this.data.viewData.responsibleId) > 0)
				{
					BX.tooltip(this.data.viewData.responsibleId, targetNode);
				}
			}
			var delayLabel = formatDelayInterval(this.getDelayInterval(),
				BX.message('CRM_AUTOMATION_CMP_AT_ONCE'),
				this.component.data['ENTITY_FIELDS']
			);

			if (this.isExecuteAfterPrevious())
			{
				delayLabel = (delayLabel !== BX.message('CRM_AUTOMATION_CMP_AT_ONCE')) ? delayLabel + ', ' : '';
				delayLabel += BX.message('CRM_AUTOMATION_CMP_AFTER_PREVIOUS');
			}

			if (this.getCondition().field !== '')
			{
				delayLabel += ', ' + BX.message('CRM_AUTOMATION_CMP_BY_CONDITION');
			}

			var delayNode;
			if (this.viewMode === Component.ViewMode.Edit)
			{
				delayNode = BX.create("a", {
					attrs: {
						className: "crm-automation-robot-link",
						title: delayLabel
					},
					text: delayLabel
				})
			}
			else
			{
				delayNode = BX.create("span", {
					attrs: { className: "crm-automation-robot-text" },
					text: delayLabel
				})
			}

			if (this.viewMode === Component.ViewMode.View)
			{
				switch (status)
				{
					case Component.LogStatus.Running:
						if (this.component.isCurrentEntityStatus(this.template.getStatusId()))
						{
							loader = BX.create("div", {
								attrs: { className: "crm-automation-robot-loader" }
							});
						}
						break;
					case Component.LogStatus.Completed:
					case Component.LogStatus.AutoCompleted:
						wrapperClass += ' crm-automation-robot-container-wrapper-complete';
						break;
				}

				var errors = this.getLogErrors();
				if (errors.length > 0)
				{
					loader = BX.create("div", {
						attrs: {
							className: "crm-automation-robot-errors",
							'data-text': errors.join('\n')
						}
					});

					HelpHint.bindToNode(loader);
				}
			}

			var titleClassName = 'crm-automation-robot-title-text';
			if (this.viewMode === Component.ViewMode.Edit)
			{
				titleClassName += ' crm-automation-robot-title-text-editable';
			}

			var div = BX.create("div", {
				attrs: {
					className: "crm-automation-robot-container",
					'data-role': 'robot-container',
					'data-type': 'item-robot',
					'data-id': this.getId()
				},
				children: [
					BX.create('div', {
						attrs: {
							className: wrapperClass
						},
						children: [
							BX.create("div", {
								attrs: { className: "crm-automation-robot-deadline" },
								children: [delayNode]
							}),
							BX.create("div", {
								attrs: {
									className: "crm-automation-robot-title"
								},
								children: [
									BX.create("div", {
										attrs: {
											className: titleClassName
										},
										html: this.clipTitle(this.getProperty('Title')),
										events: {
											click: this.viewMode === Component.ViewMode.Edit ?
												this.onTitleEditClick.bind(this) : null
										}
									})
								]
							}),
							BX.create("div", {
								attrs: { className: "crm-automation-robot-settings" },
								children: [
									BX.create("div", {
										attrs: { className: "crm-automation-robot-settings-title" },
										text: targetLabel + ':'
									}),
									targetNode
								]
							}),
							loader
						]
					})
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				this.registerItem(div);

				var deleteBtn = BX.create('SPAN', {
					attrs: {
						className: 'crm-automation-robot-btn-delete'
					}
				});
				BX.bind(deleteBtn, 'click', function(e)
				{
					e.preventDefault();
					e.stopPropagation();
					me.onDeleteButtonClick(this);
				});
				div.lastChild.appendChild(deleteBtn);

				var settingsBtn = BX.create('div', {
					attrs: {
						className: 'crm-automation-robot-btn-settings'
					},
					text: BX.message('CRM_AUTOMATION_CMP_EDIT')
				});
				BX.bind(div, 'click', function(e)
				{
					me.onSettingsButtonClick(this);
				});
				div.appendChild(settingsBtn);
				BX.addClass(div.firstChild, 'crm-automation-robot-container-wrapper-border');
			}

			return div;
		},
		onDeleteButtonClick: function(button)
		{
			BX.remove(this.node);
			this.template.deleteRobot(this);
		},
		onSettingsButtonClick: function(button)
		{
			this.template.openRobotSettingsDialog(this);
		},
		onTitleEditClick: function(e)
		{
			e.preventDefault();
			e.stopPropagation();

			var me = this, formName = 'crm_automation_robot_title_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				},
				style: {"min-width": '540px'}
			});

			var title = this.getProperty('Title');

			form.appendChild(BX.create("span", {
				attrs: { className: "crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete" },
				text: BX.message('CRM_AUTOMATION_CMP_ROBOT_NAME') + ':'
			}));

			form.appendChild(BX.create("div", {
				attrs: { className: "crm-automation-popup-settings" },
				children: [BX.create("input", {
					attrs: {
						className: 'crm-automation-popup-input',
						type: "text",
						name: "name",
						value: title
					}
				})]
			}));

			BX.addClass(this.component.node, 'automation-base-blocked');

			var popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: BX.message('CRM_AUTOMATION_CMP_ROBOT_NAME'),
				content: form,
				closeIcon: true,
				zIndex: -100,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: false,
				events: {
					onPopupClose: function(popup)
					{
						popup.destroy();
						BX.removeClass(me.component.node, 'automation-base-blocked');
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								var nameNode = form.elements.name;
								me.setProperty('Title', nameNode.value);
								me.reInit();
								me.template.modified = true;
								this.popupWindow.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			popup.show();
		},
		clipTitle: function (fullTitle)
		{
			var title = fullTitle;
			var arrTitle = title.split(" ");
			var lastWord = "<span>" + arrTitle[arrTitle.length - 1] + "</span>";

			arrTitle.splice(arrTitle.length - 1);

			title = arrTitle.join(" ") + " " + lastWord;

			return title;
		},
		updateData: function(data)
		{
			if (BX.type.isPlainObject(data))
			{
				this.data = data;
			}
			else
				throw 'Invalid data';
		},
		serialize: function()
		{
			var result = BX.clone(this.data);

			var fixData = function(data)
			{
				for (var key in data)
				{
					if (data.hasOwnProperty(key))
					{
						if (typeof(data[key]) === "boolean")
						{
							data[key] = data[key] ? 1 : 0;
						}
						else if (data[key] === null)
						{
							data[key] = '';
						}
						else if (BX.type.isPlainObject(data[key]))
						{
							fixData(data[key]);
						}
					}
				}
			};

			if (BX.type.isPlainObject(result.Properties))
			{
				fixData(result.Properties);
			}
			result.Delay = this.delay.serialize();
			result.Condition = this.condition.serialize();
			return result;
		},
		/**
		 * @returns {DelayInterval}
		 */
		getDelayInterval: function()
		{
			return this.delay;
		},
		setDelayInterval: function(delay)
		{
			this.delay = delay;
			return this;
		},
		/**
		 * @returns {Condition}
		 */
		getCondition: function()
		{
			return this.condition;
		},
		setCondition: function(condition)
		{
			this.condition = condition;
			return this;
		},
		setExecuteAfterPrevious: function(flag)
		{
			this.data.ExecuteAfterPrevious = flag ? 1 : 0;

			return this;
		},
		isExecuteAfterPrevious: function()
		{
			return (this.data.ExecuteAfterPrevious === 1)
		},
		registerItem: function(object)
		{
			object.onbxdragstart = BX.proxy(this.dragStart, this);
			object.onbxdrag = BX.proxy(this.dragMove, this);
			object.onbxdragstop = BX.proxy(this.dragStop, this);
			object.onbxdraghover = BX.proxy(this.dragOver, this);
			jsDD.registerObject(object);
			jsDD.registerDest(object, 1);
		},
		dragStart: function()
		{
			this.draggableItem = BX.proxy_context;
			this.draggableItem.className = "crm-automation-robot-container";

			if (!this.draggableItem)
			{
				jsDD.stopCurrentDrag();
				return;
			}

			if (!this.stub)
			{
				var itemWidth = this.draggableItem.offsetWidth;
				this.stub = this.draggableItem.cloneNode(true);
				this.stub.style.position = "absolute";
				this.stub.className = "crm-automation-robot-container crm-automation-robot-container-drag";
				this.stub.style.width = itemWidth + "px";
				document.body.appendChild(this.stub);
			}
		},

		dragMove: function(x,y)
		{
			this.stub.style.left = x + "px";
			this.stub.style.top = y + "px";
		},

		dragOver: function(destination, x, y)
		{
			if (this.droppableItem)
			{
				this.droppableItem.className = "crm-automation-robot-container";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "crm-automation-robot-list";
			}

			var type = destination.getAttribute("data-type");

			if (type === "item-robot")
			{
				this.droppableItem = destination;
				this.droppableColumn = null;
			}

			if (type === "column-robot")
			{
				this.droppableColumn = destination.children[0];
				this.droppableItem = null;
			}

			if (this.droppableItem)
			{
				this.droppableItem.className = "crm-automation-robot-container crm-automation-robot-container-pre";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "crm-automation-robot-list crm-automation-robot-list-pre";
			}
		},

		dragStop: function()
		{
			var isCopy = window.event.ctrlKey;

			var tpl, beforeRobot;
			if (this.draggableItem)
			{
				if (this.droppableItem)
				{
					this.droppableItem.className = "crm-automation-robot-container";
					tpl = this.templateManager.getTemplateByColumnNode(this.droppableItem.parentNode);
					if (tpl)
					{
						beforeRobot = tpl.getRobotById(this.droppableItem.getAttribute('data-id'));
						if (isCopy)
						{
							this.copyTo(tpl, beforeRobot)
						}
						else if (this !== beforeRobot)
							this.moveTo(tpl, beforeRobot);
					}
				}
				else if (this.droppableColumn)
				{
					this.droppableColumn.className = "crm-automation-robot-list";
					tpl = this.templateManager.getTemplateByColumnNode(this.droppableColumn);
					if (tpl)
					{
						isCopy ? this.copyTo(tpl) : this.moveTo(tpl);
					}
				}
			}

			this.stub.parentNode.removeChild(this.stub);
			this.stub = null;
			this.draggableItem = null;
			this.droppableItem = null;
		},
		moveTo: function(template, beforeRobot)
		{
			BX.remove(this.node);
			this.template.deleteRobot(this);
			this.template = template;

			this.template.insertRobot(this, beforeRobot);
			this.node = this.createNode();
			this.template.insertRobotNode(this.node, beforeRobot ? beforeRobot.node : null);
		},
		copyTo: function(template, beforeRobot)
		{
			var robot = new Robot(template);
			robot.init(this.serialize(), this.viewMode);
			template.insertRobot(robot, beforeRobot);
			template.insertRobotNode(robot.node, beforeRobot ? beforeRobot.node : null);
		},
		getDescriptionSettings: function()
		{
			var settings = {};
			var description = this.templateManager.getRobotDescription(this.data['Type']);
			if (description && description['ROBOT_SETTINGS'])
			{
				settings = description['ROBOT_SETTINGS'];
			}
			return settings;
		}
	};

	var TriggerManager = function(component)
	{
		this.component = component;
	};

	TriggerManager.prototype =
	{
		init: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			this.viewMode = viewMode || Component.ViewMode.View;
			this.availableTriggers = BX.type.isArray(data.AVAILABLE_TRIGGERS) ? data.AVAILABLE_TRIGGERS : [];
			this.triggersData = BX.type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];
			this.columnNodes = document.querySelectorAll('[data-type="column-trigger"]');
			this.listNodes = this.component.node.querySelectorAll('[data-role="trigger-list"]');
			this.buttonsNodes = this.component.node.querySelectorAll('[data-role="trigger-buttons"]');
			this.initButtons();
			this.initTriggers();

			this.modified = false;

			//register DD
			for(var i = 0; i < this.columnNodes.length; i++)
			{
				jsDD.registerDest(this.columnNodes[i], 10);
			}

			top.BX.addCustomEvent(
				top,
				'Rest:AppLayout:ApplicationInstall',
				this.onRestAppInstall.bind(this)
			);
		},
		reInit: function(data, viewMode)
		{
			if (!BX.type.isPlainObject(data))
				data = {};

			var i;
			this.viewMode = viewMode || Component.ViewMode.View;
			for (i = 0; i < this.listNodes.length; ++i)
			{
				BX.cleanNode(this.listNodes[i]);
			}
			for (i = 0; i < this.buttonsNodes.length; ++i)
			{
				BX.cleanNode(this.buttonsNodes[i]);
			}

			this.triggersData = BX.type.isArray(data.TRIGGERS) ? data.TRIGGERS : [];

			this.initTriggers();
			this.initButtons();

			this.modified = false;
		},
		initTriggers: function()
		{
			this.triggers = [];
			for (var i = 0; i < this.triggersData.length; ++i)
			{
				var trigger = new Trigger(this);
				trigger.init(this.triggersData[i], this.viewMode);
				this.insertTriggerNode(trigger.getStatusId(), trigger.node);
				this.triggers.push(trigger);
			}
		},
		initButtons: function()
		{
			if (this.viewMode === Component.ViewMode.Edit)
			{
				for (var i = 0; i < this.buttonsNodes.length; ++i)
				{
					this.createAddButton(this.buttonsNodes[i]);
				}
			}
		},
		createAddButton: function(containerNode)
		{
			var me = this,
				div = BX.create('a', {
							text: BX.message('CRM_AUTOMATION_CMP_ADD'),
							props: {
								href: '#'
							},
							events: {
								click: function(e)
								{
									e.preventDefault();
									me.onAddButtonClick(this);
								}
							},
							attrs: {
								className: 'crm-automation-btn-add',
								'data-status-id': containerNode.getAttribute('data-status-id')
							}
						});
			containerNode.appendChild(div);
		},
		onAddButtonClick: function(button)
		{
			var me = this, i, menuItems = [];
			var onMenuClick = function(e, item)
			{
				me.addTrigger(item.triggerData, function(trigger)
				{
					me.openTriggerSettingsDialog(trigger);
				});

				this.popupWindow.close();
			};

			for (i = 0; i < this.availableTriggers.length; ++i)
			{
				if (this.availableTriggers[i].CODE === 'APP')
				{
					menuItems.push(this.createAppTriggerMenuItem(
						button.getAttribute('data-status-id'),
						this.availableTriggers[i]
					));
					continue;
				}

				menuItems.push({
					text: this.availableTriggers[i].NAME,
					triggerData: {
						ENTITY_STATUS: button.getAttribute('data-status-id'),
						CODE: this.availableTriggers[i].CODE
					},
					onclick: onMenuClick
				});
			}

			BX.PopupMenu.show(
				Component.generateUniqueId(),
				button,
				menuItems,
				{
					autoHide: true,
					offsetLeft: (BX.pos(button)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					events : {
						onPopupClose : function() {this.destroy()}
					}
				}
			);
		},
		createAppTriggerMenuItem: function(status, triggerData)
		{
			var me = this, menuItems = [];
			var onMenuClick = function(e, item)
			{
				me.addTrigger(item.triggerData, function(trigger)
				{
					me.openTriggerSettingsDialog(trigger);
				});

				this.popupWindow.close();
			};

			for (var i = 0; i < triggerData['APP_LIST'].length; ++i)
			{
				var item = triggerData['APP_LIST'][i];
				var itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
				menuItems.push({
					text: BX.util.htmlspecialchars(itemName),
					triggerData: {
						ENTITY_STATUS: status,
						NAME: itemName,
						CODE: triggerData.CODE,
						APPLY_RULES: {
							APP_ID: item['APP_ID'],
							CODE: item['CODE']
						}
					},
					onclick: onMenuClick
				});
			}

			if (BX.getClass('BX.rest.Marketplace'))
			{
				if (menuItems.length)
					menuItems.push({delimiter: true});

				menuItems.push({
					text: BX.message('CRM_AUTOMATION_ROBOT_CATEGORY_OTHER_MARKETPLACE'),
					onclick: function()
					{
						BX.rest.Marketplace.open({PLACEMENT: 'CRM_ROBOT_TRIGGERS'});
					}
				});
			}

			return {
				text: triggerData.NAME,
				items: menuItems
			}
		},
		addTrigger: function(triggerData, callback)
		{
			var trigger = new Trigger(this);
			trigger.init(triggerData, this.viewMode);
			trigger.draft = true;
			if (callback)
				callback(trigger);
		},
		deleteTrigger: function(trigger, callback)
		{
			if (trigger.getId() > 0)
			{
				trigger.markDeleted();
			}
			else
			{
				for(var i = 0; i < this.triggers.length; ++i)
				{
					if (this.triggers[i] === trigger)
						this.triggers.splice(i, 1);
				}
			}
			if (callback)
				callback(trigger);

			this.modified = true;
		},
		insertTriggerNode: function(entityStatus, triggerNode)
		{
			var listNode = this.component.node.querySelector('[data-role="trigger-list"][data-status-id="'+entityStatus+'"]');
			listNode.appendChild(triggerNode);
		},
		serialize: function()
		{
			var triggers = [];

			for (var i = 0; i < this.triggers.length; ++i)
			{
				triggers.push(this.triggers[i].serialize());
			}

			return triggers;
		},
		getTriggerName: function(code)
		{
			for (var i = 0; i < this.availableTriggers.length; ++i)
			{
				if (code == this.availableTriggers[i]['CODE'])
					return this.availableTriggers[i]['NAME'];
			}
			return code;
		},
		needSave: function()
		{
			return this.modified;
		},
		openTriggerSettingsDialog: function(trigger)
		{
			var me = this, formName = 'crm_automation_trigger_dialog';

			var form = BX.create('form', {
				props: {
					name: formName
				},
				style: {"min-width": '540px'}
			});

			var iconHelp = BX.create('div', {
				attrs: { className: 'crm-automation-robot-help' },
				events: {click: BX.delegate(this.component.onGlobalHelpClick, this.component)}
			});
			form.appendChild(iconHelp);

			var title = this.getTriggerName(trigger.data['CODE']);

			form.appendChild(BX.create("span", {
				attrs: { className: "crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete" },
				text: BX.message('CRM_AUTOMATION_CMP_TRIGGER_NAME') + ':'
			}));

			form.appendChild(BX.create("div", {
				attrs: { className: "crm-automation-popup-settings" },
				children: [BX.create("input", {
					attrs: {
						className: 'crm-automation-popup-input',
						type: "text",
						name: "name",
						value: trigger.data['NAME'] || title
					}
				})]
			}));

			if (trigger.data['CODE'] == 'WEBHOOK')
			{
				if (!BX.type.isPlainObject(trigger.data['APPLY_RULES']))
					trigger.data['APPLY_RULES'] = {};

				if (!trigger.data['APPLY_RULES']['code'])
					trigger.data['APPLY_RULES']['code'] = BX.util.getRandomString(5);

				form.appendChild(BX.create("span", {
					attrs: { className: "crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete" },
					text: "URL:"
				}));

				form.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						value: trigger.data['APPLY_RULES']['code'],
						name: 'code'
					}
				}));

				var hookLinkTextarea = BX.create("textarea", {
					attrs: {
						className: "crm-automation-popup-textarea",
						placeholder: "...",
						readonly: 'readonly',
						name: 'webhook_handler'
					},
					events: {
						click: function(e) {this.select();}
					}
				});

				form.appendChild(BX.create("div", {
					attrs: { className: "crm-automation-popup-settings" },
					children: [hookLinkTextarea]
				}));

				form.appendChild(BX.create("span", {
					attrs: { className: "crm-automation-popup-settings-title crm-automation-popup-settings-title-autocomplete" },
					text: BX.message('CRM_AUTOMATION_CMP_WEBHOOK_ID')
				}));

				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: this.component.getAjaxUrl(),
					data: {
						ajax_action: 'get_webhook_handler',
						entity_type_id: this.component.entityTypeId
					},
					onsuccess: function(response)
					{
						if (response['DATA']['HANDLER'])
						{
							var url = window.location.protocol + '//' + window.location.host + response['DATA']['HANDLER'];
							url = BX.util.add_url_param(url, {code: trigger.data['APPLY_RULES']['code']});
							hookLinkTextarea.value = url;
						}
					}
				});
			}
			else if (trigger.data['CODE'] == 'WEBFORM')
			{
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: this.component.getAjaxUrl(),
					data: {
						ajax_action: 'get_webform_forms'
					},
					onsuccess: function(response)
					{
						if (response['DATA']['forms'])
						{
							var select = BX.create('select', {
								attrs: {className: 'crm-automation-popup-settings-dropdown'},
								props: {
									name: 'form_id',
									value: ''
								},
								children: [BX.create('option', {
									props: {value: ''},
									text: BX.message('CRM_AUTOMATION_TRIGGER_WEBFORM_ANY')
								})]
							});

							for (var i = 0; i < response['DATA']['forms'].length; ++i)
							{
								var item = response['DATA']['forms'][i];
								select.appendChild(BX.create('option', {
									props: {value: item['ID']},
									text: item['NAME']
								}));
							}
							if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['form_id'])
							{
								select.value = trigger.data['APPLY_RULES']['form_id'];
							}

							var div = BX.create('div', {attrs: {className: 'crm-automation-popup-settings'},
								children: [BX.create('span', {attrs: {
									className: 'crm-automation-popup-settings-title'
								}, text: BX.message('CRM_AUTOMATION_TRIGGER_WEBFORM_LABEL') + ':'}), select]
							});
							form.appendChild(div);
						}
					}
				});
			}
			else if (trigger.data['CODE'] == 'OPENLINE')
			{
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: this.component.getAjaxUrl(),
					data: {
						ajax_action: 'get_openline_configs'
					},
					onsuccess: function(response)
					{
						if (response['DATA']['configs'])
						{
							var select = BX.create('select', {
								attrs: {className: 'crm-automation-popup-settings-dropdown'},
								props: {
									name: 'config_id',
									value: ''
								},
								children: [BX.create('option', {
									props: {value: ''},
									text: BX.message('CRM_AUTOMATION_TRIGGER_WEBFORM_ANY')
								})]
							});

							for (var i = 0; i < response['DATA']['configs'].length; ++i)
							{
								var item = response['DATA']['configs'][i];
								select.appendChild(BX.create('option', {
									props: {value: item['ID']},
									text: item['NAME']
								}));
							}
							if (BX.type.isPlainObject(trigger.data['APPLY_RULES']) && trigger.data['APPLY_RULES']['config_id'])
							{
								select.value = trigger.data['APPLY_RULES']['config_id'];
							}

							var div = BX.create('div', {attrs: {className: 'crm-automation-popup-settings'},
								children: [BX.create('span', {attrs: {
									className: 'crm-automation-popup-settings-title'
								}, text: BX.message('CRM_AUTOMATION_TRIGGER_OPENLINE_LABEL') + ':'}), select]
							});
							form.appendChild(div);
						}
					}
				});
			}

			BX.addClass(this.component.node, 'automation-base-blocked');

			var popup = new BX.PopupWindow(Component.generateUniqueId(), null, {
				titleBar: title,
				content: form,
				closeIcon: true,
				zIndex: -100,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: false,
				events: {
					onPopupClose: function(popup)
					{
						popup.destroy();
						BX.removeClass(me.component.node, 'automation-base-blocked');
					}
				},
				buttons: [
					new BX.PopupWindowButton({
						text : BX.message('JS_CORE_WINDOW_SAVE'),
						className : "popup-window-button-accept",
						events : {
							click: function() {
								var formData = BX.ajax.prepareForm(form);
								trigger.data['NAME'] = formData['data']['name'];

								if (trigger.data['CODE'] == 'WEBFORM')
								{
									trigger.data['APPLY_RULES'] = {
										form_id:  formData['data']['form_id']
									}
								}
								if (trigger.data['CODE'] == 'OPENLINE')
								{
									trigger.data['APPLY_RULES'] = {
										config_id:  formData['data']['config_id']
									}
								}

								if (trigger.data['CODE'] == 'WEBHOOK')
								{
									trigger.data['APPLY_RULES'] = {
										code: formData['data']['code']
									}
								}

								if (trigger.draft)
								{
									me.triggers.push(trigger);
									me.insertTriggerNode(trigger.getStatusId(), trigger.node)
								}
								delete trigger.draft;

								trigger.reInit();
								me.modified = true;
								this.popupWindow.close();
							}
						}
					}),
					new BX.PopupWindowButtonLink({
						text : BX.message('JS_CORE_WINDOW_CANCEL'),
						className : "popup-window-button-link-cancel",
						events : {
							click: function(){
								this.popupWindow.close()
							}
						}
					})
				]
			});

			popup.show();
		},
		onRestAppInstall: function(installed, eventResult)
		{
			eventResult.redirect = false;
			var me = this;

			setTimeout(function()
			{
				BX.ajax({
					method: 'POST',
					dataType: 'json',
					url: me.component.getAjaxUrl(),
					data: {
						ajax_action: 'get_available_triggers',
						entity_type_id: me.component.entityTypeId
					},
					onsuccess: function(response)
					{
						if (BX.type.isArray(response['DATA']))
						{
							me.availableTriggers = response['DATA'];
						}
					}
				});
			}, 1500);
		}
	};

	var Trigger = function(manager)
	{
		this.manager = manager;
		this.component = manager.component;
		this.tracker = manager.component.tracker;
		this.data = {};
		this.deleted = false;
		this.draggableItem = null;
		this.droppableItem = null;
		this.droppableColumn = null;
		this.stub = null;
		this.column = null;
	};

	Trigger.prototype =
	{
		init: function(data, viewMode)
		{
			if (data)
				this.data = data;

			this.viewMode = viewMode || Component.ViewMode.View;
			this.node = this.createNode();
		},
		reInit: function(data, viewMode)
		{
			var node = this.node;
			this.node = this.createNode();
			if (node.parentNode)
				node.parentNode.replaceChild(this.node, node);
		},
		getId: function()
		{
			return this.data['ID'] || 0;
		},
		getStatusId: function()
		{
			return this.data['ENTITY_STATUS'] || '';
		},
		getLogStatus: function()
		{
			var log = this.tracker.getTriggerLog(this.getId());
			return log ? parseInt(log['STATUS']) : null;
		},
		createNode: function()
		{
			var me = this, status = this.getLogStatus();

			var wrapperClass = 'crm-automation-trigger-item-wrapper';

			if (this.viewMode === Component.ViewMode.Edit)
			{
				wrapperClass += ' crm-automation-trigger-item-wrapper-draggable';

				var settingsBtn = BX.create("div", {
					attrs: {
						className: "crm-automation-trigger-item-wrapper-edit",
						style: "border-color: " + "#acf2fa"
					},
					text: BX.message('CRM_AUTOMATION_CMP_EDIT')
				});
			}
			else
			{
				if (status == Component.LogStatus.Completed)
				{
					wrapperClass += ' crm-automation-trigger-item-wrapper-complete';
				}
				else if (this.component.isPreviousEntityStatus(this.getStatusId()))
				{
					wrapperClass += ' crm-automation-trigger-item-wrapper-complete-light';
				}
			}

			var triggerName = this.data['NAME'];
			if (!triggerName)
			{
				triggerName = this.manager.getTriggerName(this.data['CODE']);
			}

			var div = BX.create('DIV', {
				attrs: {
					'data-role': 'trigger-container',
					className: 'crm-automation-trigger-item',
					'data-type': 'item-trigger'
				},
				children: [
					BX.create("div", {
						attrs: {
							className: wrapperClass,
							style: "border-color: " + "#acf2fa"
						},
						children: [
							BX.create("div", {
								attrs: { className: "crm-automation-trigger-item-wrapper-text" },
								text: triggerName
							})
						]
					}),
					settingsBtn
				]
			});

			if (this.viewMode === Component.ViewMode.Edit)
			{
				this.registerItem(div);

				var deleteBtn = BX.create('SPAN', {
					attrs: {
						'data-role': 'btn-delete-trigger',
						className: 'crm-automation-trigger-btn-delete'
					}
				});

				BX.bind(deleteBtn, 'click', function(e)
				{
					e.preventDefault();
					e.stopPropagation();
					me.onDeleteButtonClick(this);
				});

				div.appendChild(deleteBtn);
				BX.addClass(div.firstChild, 'crm-automation-trigger-item-wrapper-border');
			}

			if (this.viewMode === Component.ViewMode.Edit)
			{
				BX.bind(div, 'click', function(e)
				{
					me.onSettingsButtonClick(this);
				});
			}

			return div;
		},
		onSettingsButtonClick: function(button)
		{
			this.manager.openTriggerSettingsDialog(this);
		},
		registerItem: function(object)
		{
			object.onbxdragstart = BX.proxy(this.dragStart, this);
			object.onbxdrag = BX.proxy(this.dragMove, this);
			object.onbxdragstop = BX.proxy(this.dragStop, this);
			object.onbxdraghover = BX.proxy(this.dragOver, this);
			jsDD.registerObject(object);
			jsDD.registerDest(object, 1);
		},
		dragStart: function()
		{
			this.draggableItem = BX.proxy_context;
			this.draggableItem.className = "crm-automation-trigger-item";

			if (!this.draggableItem)
			{
				jsDD.stopCurrentDrag();
				return;
			}

			if (!this.stub)
			{
				var itemWidth = this.draggableItem.offsetWidth;
				this.stub = this.draggableItem.cloneNode(true);
				this.stub.style.position = "absolute";
				this.stub.className = "crm-automation-trigger-item crm-automation-trigger-item-drag";
				this.stub.style.width = itemWidth + "px";
				document.body.appendChild(this.stub);
			}
		},

		dragMove: function(x,y)
		{
			this.stub.style.left = x + "px";
			this.stub.style.top = y + "px";
		},

		dragOver: function(destination, x, y)
		{
			if (this.droppableItem)
			{
				this.droppableItem.className = "crm-automation-trigger-item";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "crm-automation-trigger-list";
			}

			var type = destination.getAttribute("data-type");


			if (type === "item-trigger")
			{
				this.droppableItem = destination;
				this.droppableColumn = null;
			}

			if (type === "column-trigger")
			{
				this.droppableColumn = destination.children[0];
				this.droppableItem = null;
			}

			if (this.droppableItem)
			{
				this.droppableItem.className = "crm-automation-trigger-item crm-automation-trigger-item-pre";
			}

			if (this.droppableColumn)
			{
				this.droppableColumn.className = "crm-automation-trigger-list crm-automation-trigger-list-pre";
			}
		},

		dragStop: function()
		{
			var trigger, isCopy = window.event.ctrlKey;
			var copyTrigger = function(parent, statusId)
			{
				var trigger = new Trigger(parent.manager);
				var initData = parent.serialize();
				delete initData['ID'];
				if (initData['CODE'] === 'WEBHOOK')
				{
					initData['APPLY_RULES'] = {};
				}
				initData['ENTITY_STATUS'] = statusId;
				trigger.init(initData, parent.viewMode);
				return trigger;
			};

			if (this.draggableItem)
			{
				if (this.droppableItem)
				{
					this.droppableItem.className = "crm-automation-trigger-item";
					var thisColumn = this.droppableItem.parentNode;
					if (!isCopy)
					{
						thisColumn.insertBefore(this.draggableItem, this.droppableItem);
						this.moveTo(thisColumn.getAttribute('data-status-id'));
					}
					else
					{
						trigger = copyTrigger(this, thisColumn.getAttribute('data-status-id'));
						thisColumn.insertBefore(trigger.node, this.droppableItem);

					}
				}
				else if (this.droppableColumn)
				{
					this.droppableColumn.className = "crm-automation-trigger-list";
					if (!isCopy)
					{
						this.droppableColumn.appendChild(this.draggableItem);
						this.moveTo(this.droppableColumn.getAttribute('data-status-id'));
					}
					else
					{
						trigger = copyTrigger(this, this.droppableColumn.getAttribute('data-status-id'));
						this.droppableColumn.appendChild(trigger.node);
					}
				}

				if (trigger)
				{
					this.manager.triggers.push(trigger);
					this.manager.modified = true;
				}
			}

			this.stub.parentNode.removeChild(this.stub);
			this.stub = null;
			this.draggableItem = null;
			this.droppableItem = null;
		},

		onDeleteButtonClick: function(button)
		{
			BX.remove(button.parentNode);
			this.manager.deleteTrigger(this);
		},
		updateData: function(data)
		{
			if (BX.type.isPlainObject(data))
			{
				this.data = data;
			}
			else
				throw 'Invalid data';
		},
		markDeleted: function()
		{
			this.deleted = true;
			return this;
		},
		serialize: function()
		{
			var data = BX.clone(this.data);
			if (this.deleted)
				data['DELETED'] = 'Y';

			return data;
		},
		moveTo: function(statusId)
		{
			this.data['ENTITY_STATUS'] = statusId;
			//TODO: ref.
			this.manager.modified = true;
		}
	};

	var Tracker = function(component)
	{
		this.component = component;
	};

	Tracker.prototype =
	{
		init: function(log)
		{
			if (!BX.type.isPlainObject(log))
				log = {};

			this.log = log;
			this.triggers = {};
			this.robots = {};

			for (var statusId in log)
			{
				if (!log.hasOwnProperty(statusId))
					continue;

				if (log[statusId]['trigger'])
				{
					this.triggers[log[statusId]['trigger']['ID']] = log[statusId]['trigger'];
				}

				if (log[statusId]['robots'])
				{
					for (var robotId in log[statusId]['robots'])
					{
						if (!log[statusId]['robots'].hasOwnProperty(robotId))
							continue;

						this.robots[robotId] = log[statusId]['robots'][robotId];
					}
				}
			}
		},
		reInit: function(log)
		{
			this.init(log);
		},
		getRobotLog: function(id)
		{
			return this.robots[id] || null;
		},
		getTriggerLog: function(id)
		{
			return this.triggers[id] || null;
		}
	};

	// -> Destination
	var Destination = function(component, container)
	{
		var me = this;

		var config, configString = container.getAttribute('data-config');
		if (configString)
		{
			config = BX.parseJSON(configString);
		}

		if (!BX.type.isPlainObject(config))
			config = {};

		this.container = container;
		this.itemsNode = BX.create('span');
		this.inputBoxNode = BX.create('span', {
			attrs: {
				className: 'feed-add-destination-input-box'
			}
		});
		this.inputNode = BX.create('input', {
			props: {
				type: 'text'
			},
			attrs: {
				className: 'feed-add-destination-inp'
			}
		});

		this.inputBoxNode.appendChild(this.inputNode);

		this.tagNode = BX.create('a', {
			attrs: {
				className: 'feed-add-destination-link'
			}
		});

		BX.addClass(container, 'crm-automation-popup-autocomplete');

		container.appendChild(this.itemsNode);
		container.appendChild(this.inputBoxNode);
		container.appendChild(this.tagNode);

		this.component = component;
		this.itemTpl = config.itemTpl;

		this.data = null;
		this.dialogId = Component.generateUniqueId();
		this.createValueNode(config.valueInputName || '');
		this.selected = config.selected ? BX.clone(config.selected) : [];
		this.selectOne = !config.multiple;
		this.required = config.required || false;
		this.additionalFields = BX.type.isArray(config.additionalFields) ? config.additionalFields : [];

		BX.bind(this.tagNode, 'focus', function(e) {
			e.preventDefault();
			me.openDialog({bByFocusEvent: true});
		});
		BX.bind(this.container, 'click', function(e) {
			e.preventDefault();
			me.openDialog();
		});

		this.addItems(this.selected);

		this.tagNode.innerHTML = (
			this.selected.length <= 0
				? BX.message('CRM_AUTOMATION_CMP_CHOOSE')
				: BX.message('CRM_AUTOMATION_CMP_EDIT')
		);
	};

	Destination.prototype = {
		getData: function(next)
		{
			var me = this;

			if (me.ajaxProgress)
				return;

			me.ajaxProgress = true;
			BX.ajax({
				method: 'POST',
				dataType: 'json',
				url: me.component.getAjaxUrl(),
				data: {
					ajax_action: 'get_destination_data',
					entity_type_id: me.component.entityTypeId
				},
				onsuccess: function (response)
				{
					me.data = response.DATA || {};
					me.ajaxProgress = false;
					me.initDialog(next);
				}
			});
		},
		initDialog: function(next)
		{
			var i, me = this, data = this.data;

			if (!data)
			{
				me.getData(next);
				return;
			}

			var itemsSelected = {};
			for (i = 0; i < me.selected.length; ++i)
			{
				itemsSelected[me.selected[i].id] = me.selected[i].entityType
			}

			var items = {
				users : data.USERS || {},
				department : data.DEPARTMENT || {},
				departmentRelation : data.DEPARTMENT_RELATION || {},
				bpuserroles : data.ROLES || {}
			};
			var itemsLast =  {
				users: data.LAST.USERS || {},
				bpuserroles : data.LAST.ROLES || {}
			};

			for (i = 0; i < this.additionalFields.length; ++i)
			{
				items.bpuserroles[this.additionalFields[i]['id']] = this.additionalFields[i];
			}

			if (!items["departmentRelation"])
			{
				items["departmentRelation"] = BX.SocNetLogDestination.buildDepartmentRelation(items["department"]);
			}

			if (!me.inited)
			{
				me.inited = true;
				var destinationInput = me.inputNode;
				destinationInput.id = me.dialogId + 'input';

				var destinationInputBox = me.inputBoxNode;
				destinationInputBox.id = me.dialogId + 'input-box';

				var tagNode = this.tagNode;
				tagNode.id = this.dialogId + 'tag';

				var itemsNode = me.itemsNode;

				BX.SocNetLogDestination.init({
					name : me.dialogId,
					searchInput : destinationInput,
					extranetUser :  false,
					bindMainPopup : {node: me.container, offsetTop: '5px', offsetLeft: '15px'},
					bindSearchPopup : {node: me.container, offsetTop : '5px', offsetLeft: '15px'},
					departmentSelectDisable: true,
					sendAjaxSearch: true,
					callback : {
						select : function(item, type, search, bUndeleted)
						{
							me.addItem(item, type);
							if (me.selectOne)
								BX.SocNetLogDestination.closeDialog();
						},
						unSelect : function (item)
						{
							if (me.selectOne)
								return;
							me.unsetValue(item.entityId);
							BX.SocNetLogDestination.BXfpUnSelectCallback.call({
								formName: me.dialogId,
								inputContainerName: itemsNode,
								inputName: destinationInput.id,
								tagInputName: tagNode.id,
								tagLink1: BX.message('CRM_AUTOMATION_CMP_CHOOSE'),
								tagLink2: BX.message('CRM_AUTOMATION_CMP_EDIT')
							}, item)
						},
						openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						}),
						closeSearch : BX.delegate(BX.SocNetLogDestination.BXfpCloseSearchCallback, {
							inputBoxName: destinationInputBox.id,
							inputName: destinationInput.id,
							tagInputName: tagNode.id
						})
					},
					items : items,
					itemsLast : itemsLast,
					itemsSelected : itemsSelected,
					useClientDatabase: false,
					destSort: data.DEST_SORT || {},
					allowAddUser: false
				});

				BX.onCustomEvent(BX.SocNetLogDestination, "onTabsAdd", [me.dialogId, {
					id: 'bpuserrole',
					name: BX.message('CRM_AUTOMATION_CMP_USER_SELECTOR_TAB'),
					itemType: 'bpuserroles',
					dialogGroup: {
						groupCode: 'bpuserroles',
						title: BX.message('CRM_AUTOMATION_CMP_USER_SELECTOR_TAB')
					}
				}]);

				BX.bind(destinationInput, 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
					formName: me.dialogId,
					inputName: destinationInput.id,
					tagInputName: tagNode.id
				}));
				BX.bind(destinationInput, 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
					formName: me.dialogId,
					inputName: destinationInput.id
				}));

				BX.SocNetLogDestination.BXfpSetLinkName({
					formName: me.dialogId,
					tagInputName: tagNode.id,
					tagLink1: BX.message('CRM_AUTOMATION_CMP_CHOOSE'),
					tagLink2: BX.message('CRM_AUTOMATION_CMP_EDIT')
				});
			}
			next();
		},
		addItem: function(item, type)
		{
			var me = this;
			var destinationInput = this.inputNode;
			var tagNode = this.tagNode;
			var items = this.itemsNode;

			if (!BX.findChild(items, { attr : { 'data-id' : item.id }}, false, false))
			{
				if (me.selectOne && me.inited)
				{
					var toRemove = [];
					for (var i = 0; i < items.childNodes.length; ++i)
					{
						toRemove.push({
							itemId: items.childNodes[i].getAttribute('data-id'),
							itemType: items.childNodes[i].getAttribute('data-type')
						})
					}

					me.initDialog(function() {
						for (var i = 0; i < toRemove.length; ++i)
						{
							BX.SocNetLogDestination.deleteItem(toRemove[i].itemId, toRemove[i].itemType, me.dialogId);
						}
					});

					BX.cleanNode(items);
					me.cleanValue();
				}

				var container = this.createItemNode({
					text: item.name,
					deleteEvents: {
						click: function(e) {
							if (me.selectOne && me.required)
							{
								me.openDialog();
							}
							else
							{
								me.initDialog(function() {
									BX.SocNetLogDestination.deleteItem(item.id, type, me.dialogId);
									BX.remove(container);
									me.unsetValue(item.entityId);
								});
							}
							e.preventDefault();
						}
					}
				});

				this.setValue(item.entityId);

				container.setAttribute('data-id', item.id);
				container.setAttribute('data-type', type);

				items.appendChild(container);

				if (!item.entityType)
					item.entityType = type;
			}

			destinationInput.value = '';
			tagNode.innerHTML = BX.message('CRM_AUTOMATION_CMP_EDIT');
		},
		addItems: function(items)
		{
			for(var i = 0; i < items.length; ++i)
			{
				this.addItem(items[i], items[i].entityType)
			}
		},
		openDialog: function(params)
		{
			var me = this;
			this.initDialog(function()
			{
				BX.SocNetLogDestination.openDialog(me.dialogId, params);
			})
		},
		destroy: function()
		{
			if (this.inited)
			{
				if (BX.SocNetLogDestination.isOpenDialog())
				{
					BX.SocNetLogDestination.closeDialog();
				}
				BX.SocNetLogDestination.closeSearch();
			}
		},
		createItemNode: function(options)
		{
			return BX.create('span', {
				attrs: {
					className: 'crm-automation-popup-autocomplete-item'
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'crm-automation-popup-autocomplete-name'
						},
						html: options.text || ''
					}),
					BX.create('span', {
						attrs: {
							className: 'crm-automation-popup-autocomplete-delete'
						},
						events: options.deleteEvents
					})
				]
			});
		},
		createValueNode: function(valueInputName)
		{
			this.valueNode = BX.create('input', {
				props: {
					type: 'hidden',
					name: valueInputName
				}
			});

			this.container.appendChild(this.valueNode);
		},
		setValue: function(value)
		{
			if (/^\d+$/.test(value))
				value = '['+ value +']';

			if (this.selectOne)
				this.valueNode.value = value;
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(';');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || value == pairs[i])
						continue;
					newVal.push(pairs[i]);
				}
				newVal.push(value);
				this.valueNode.value = newVal.join(';');
			}

		},
		unsetValue: function(value)
		{
			if (/^\d+$/.test(value))
				value = '['+ value +']';

			if (this.selectOne)
				this.valueNode.value = '';
			else
			{
				var i, newVal = [], pairs = this.valueNode.value.split(';');
				for (i = 0; i < pairs.length; ++i)
				{
					if (!pairs[i] || value == pairs[i])
						continue;
					newVal.push(pairs[i]);
				}
				this.valueNode.value = newVal.join(';');
			}
		},
		cleanValue: function()
		{
			this.valueNode.value = '';
		}
	};
	// <- Destination
	// -> FileSelector
	var FileSelector = function(component, container)
	{
		var config, configString = container.getAttribute('data-config');
		if (configString)
		{
			config = BX.parseJSON(configString);
		}

		if (!BX.type.isPlainObject(config))
			config = {};

		this.container = container;

		//read configuration
		this.type = config.type || FileSelector.Type.File;
		if (config.selected && !config.selected.length)
		{
			this.type = FileSelector.Type.None;
		}

		this.multiple = config.multiple || false;
		this.required = config.required || false;
		this.valueInputName = config.valueInputName || '';
		this.typeInputName = config.typeInputName || '';
		this.useDisk = config.useDisk || false;
		this.label = config.label || 'Attachment';
		this.labelFile = config.labelFile || 'File';
		this.labelDisk = config.labelDisk || 'Disk';

		this.setFileFields(component.data['ENTITY_FIELDS']);
		this.createDom();

		if (config.selected && config.selected.length > 0)
		{
			this.addItems(BX.clone(config.selected));
		}
	};

	FileSelector.Type = {None: '', Disk: 'disk', File: 'file'};

	FileSelector.prototype =
	{
		setFileFields: function(documentFields)
		{
			var fields = [];
			for (var i = 0; i < documentFields.length; ++i)
			{
				if (documentFields[i]['Type'] === 'file')
				{
					fields.push(documentFields[i]);
				}
			}
			this.fileFields = fields;
			return this;
		},

		createDom: function()
		{
			this.container.appendChild(this.createBaseNode());
			this.showTypeControllerLayout(this.type);
		},
		createBaseNode: function()
		{
			var idSalt = Component.generateUniqueId();
			var typeRadio1 = null;

			if (this.fileFields.length > 0)
			{
				typeRadio1 = BX.create("input", {
					attrs: {
						className: "crm-automation-popup-select-input",
						type: "radio",
						id: "type-1" + idSalt,
						name: this.typeInputName,
						value: FileSelector.Type.File
					}
				});
				if (this.type === FileSelector.Type.File)
				{
					typeRadio1.setAttribute('checked', 'checked');
				}
			}

			var typeRadio2 = BX.create("input", {
				attrs: {
					className: "crm-automation-popup-select-input",
					type: "radio",
					id: "type-2" + idSalt,
					name: this.typeInputName,
					value: FileSelector.Type.Disk
				}
			});

			if (this.type === FileSelector.Type.Disk)
			{
				typeRadio2.setAttribute('checked', 'checked');
			}

			var children = [BX.create("span", {
				attrs: { className: "crm-automation-popup-settings-title" },
				text: this.label + ":"
			})];

			if (typeRadio1)
			{
				children.push(typeRadio1, BX.create("label", {
					attrs: {
						className: "crm-automation-popup-settings-link",
						for: "type-1" + idSalt
					},
					text: this.labelFile,
					events: {
						click: this.onTypeChange.bind(this, FileSelector.Type.File)
					}
				}));
			}

			children.push(typeRadio2, BX.create("label", {
				attrs: {
					className: "crm-automation-popup-settings-link",
					for: "type-2" + idSalt
				},
				text: this.labelDisk,
				events: {
					click: this.onTypeChange.bind(this, FileSelector.Type.Disk)
				}
			}));

			return BX.create("div", {
				attrs: { className: "crm-automation-popup-settings" },
				children: [
					BX.create("div", {
						attrs: { className: "crm-automation-popup-settings-block" },
						children: children
					})
				]
			});
		},
		showTypeControllerLayout: function(type)
		{
			if (type === FileSelector.Type.Disk)
			{
				this.hideFileControllerLayout();
				this.showDiskControllerLayout();
			}
			else if (type === FileSelector.Type.File)
			{
				this.hideDiskControllerLayout();
				this.showFileControllerLayout();
			}
			else
			{
				this.hideFileControllerLayout();
				this.hideDiskControllerLayout();
			}
		},
		showDiskControllerLayout: function()
		{
			if (!this.diskControllerNode)
			{
				this.diskControllerNode = BX.create('div');
				this.container.appendChild(this.diskControllerNode);
				var diskUploader = this.getDiskUploader();
				diskUploader.layout(this.diskControllerNode);
				diskUploader.show(true);
			}
			else
			{
				BX.show(this.diskControllerNode);
			}
		},
		hideDiskControllerLayout: function()
		{
			if (this.diskControllerNode)
			{
				BX.hide(this.diskControllerNode);
			}
		},
		showFileControllerLayout: function()
		{
			if (!this.fileControllerNode)
			{
				this.fileItemsNode = BX.create('span');
				this.fileControllerNode = BX.create('div', {children: [this.fileItemsNode]});
				this.container.appendChild(this.fileControllerNode);
				var addButtonNode = BX.create('a', {
					attrs: {className: 'crm-automation-popup-settings-link crm-automation-popup-settings-link-thin'},
					text: BX.message('CRM_AUTOMATION_CMP_ADD')
				});

				this.fileControllerNode.appendChild(addButtonNode);

				BX.bind(addButtonNode, 'click', this.onFileFieldAddClick.bind(this, addButtonNode));
			}
			else
			{
				BX.show(this.fileControllerNode);
			}
		},
		hideFileControllerLayout: function()
		{
			if (this.fileControllerNode)
			{
				BX.hide(this.fileControllerNode);
			}
		},
		getDiskUploader: function()
		{
			if (!this.diskUploader)
			{
				this.diskUploader = BX.CrmDiskUploader.create(
					'',
					{
						msg:
							{
								'diskAttachFiles' : BX.message('CRM_AUTOMATION_CMP_DISK_ATTACH_FILE'),
								'diskAttachedFiles' : BX.message('CRM_AUTOMATION_CMP_DISK_ATTACHED_FILES'),
								'diskSelectFile' : BX.message('CRM_AUTOMATION_CMP_DISK_SELECT_FILE'),
								'diskSelectFileLegend' : BX.message('CRM_AUTOMATION_CMP_DISK_SELECT_FILE_LEGEND'),
								'diskUploadFile' : BX.message('CRM_AUTOMATION_CMP_DISK_UPLOAD_FILE'),
								'diskUploadFileLegend' : BX.message('CRM_AUTOMATION_CMP_DISK_UPLOAD_FILE_LEGEND')
							}
					}
				);

				this.diskUploader.setMode(1);
			}

			return this.diskUploader;
		},
		onTypeChange: function(newType)
		{
			if (this.type !== newType)
			{
				this.type = newType;
				this.showTypeControllerLayout(this.type);
			}
		},
		isFileItemSelected: function(item)
		{
			var itemNode = this.fileItemsNode.querySelector('[data-file-id="'+item.id+'"]');
			return !!itemNode;
		},
		addFileItem: function(item)
		{
			if (this.isFileItemSelected(item))
			{
				return false;
			}

			var node = this.createFileItemNode(item);
			if (!this.multiple)
			{
				BX.cleanNode(this.fileItemsNode)
			}

			this.fileItemsNode.appendChild(node);
		},
		addItems: function(items)
		{
			if (this.type === FileSelector.Type.File)
			{
				for(var i = 0; i < items.length; ++i)
				{
					this.addFileItem(items[i])
				}
			}
			else
			{
				this.getDiskUploader()
					.setValues(
						this.convertToDiskItems(items)
					);
			}
		},
		convertToDiskItems: function(items)
		{
			var diskItems = [];
			for (var i = 0; i < items.length; ++i)
			{
				var item = items[i];
				diskItems.push({
					ID: item['id'],
					NAME: item['name'],
					SIZE: item['size'],
					VIEW_URL: ''
				});
			}

			return diskItems;
		},
		removeFileItem: function(item)
		{
			var itemNode = this.fileItemsNode.querySelector('[data-file-id="'+item.id+'"]');
			if (itemNode)
			{
				this.fileItemsNode.removeChild(itemNode);
			}
		},
		onFileFieldAddClick: function(addButtonNode, e)
		{
			var me = this, i, menuItems = [];

			var fields = this.fileFields;
			for (i = 0; i < fields.length; ++i)
			{
				menuItems.push({
					text: fields[i]['Name'],
					field: fields[i],
					onclick: function(e, item)
					{
						this.popupWindow.close();
						me.onFieldSelect(item.field);
					}
				});
			}

			if (!this.menuId)
			{
				this.menuId = Component.generateUniqueId();
			}

			BX.PopupMenu.show(
				this.menuId,
				addButtonNode,
				menuItems,
				{
					zIndex: 200,
					autoHide: true,
					offsetLeft: (BX.pos(addButtonNode)['width'] / 2),
					angle: { position: 'top', offset: 0 }
				}
			);
			this.menu = BX.PopupMenu.currentItem;
			e.preventDefault();
		},
		onFieldSelect: function(field)
		{
			this.addFileItem({
				id: field.Expression,
				name: field.Name,
				type: FileSelector.Type.File
			});
		},
		destroy: function()
		{
			if (this.menu)
			{
				this.menu.popupWindow.close();
			}
		},
		createFileItemNode: function(item)
		{
			return BX.create('span', {
				attrs: {
					className: 'crm-automation-popup-autocomplete-item',
					'data-file-id': item.id
				},
				children: [
					BX.create('span', {
						attrs: {
							className: 'crm-automation-popup-autocomplete-name'
						},
						text: item.name || ''
					}),
					BX.create('span', {
						attrs: {
							className: 'crm-automation-popup-autocomplete-delete'
						},
						events: {
							click: this.removeFileItem.bind(this, item)
						}
					})
				]
			});
		},
		onBeforeSave: function()
		{
			var ids = [];
			if (this.type === FileSelector.Type.Disk)
			{
				ids = this.getDiskUploader().getValues();
			}
			else if (this.type === FileSelector.Type.File)
			{
				this.fileItemsNode.childNodes.forEach(function(node)
				{
					var id = node.getAttribute('data-file-id');
					if (id !== '')
					{
						ids.push(id);
					}
				})
			}

			for (var i = 0; i < ids.length; ++i)
			{
				this.container.appendChild(BX.create('input', {
					props: {
						type: 'hidden',
						name: this.valueInputName + (this.multiple ? '[]' : ''),
						value: ids[i]
					}
				}));
			}
		}
	};
	// <- FileSelector
	// -> InlineSelector
	var InlineSelector = function(component, targetInput)
	{
		var me = this;
		this.component = component;
		this.targetInput = BX.clone(targetInput);
		this.menuButton = BX.create('span', {
			attrs: {className: 'crm-automation-popup-select-dotted'},
			events: {
				click: BX.delegate(me.openMenu, this)
			}
		});

		var wrapper = BX.create('div', {
			attrs: {className: 'crm-automation-popup-select'},
			children: [
				this.targetInput,
				this.menuButton
			]
		});

		targetInput.parentNode.replaceChild(wrapper, targetInput);

		BX.bind(this.targetInput, 'keydown', function(e) {
			me.onKeyDown(this, e);
		});
		this.targetInput.setAttribute('autocomplete', 'off');

		var fieldType = this.targetInput.getAttribute('data-selector-type');
		if (fieldType === 'date' || fieldType === 'UF:date' || fieldType === 'datetime')
		{
			BX.bind(this.targetInput, 'click', function(e)
			{
				BX.calendar({node: this, field: this, bTime: fieldType === 'datetime'});
			});
		}

		this.replaceOnWrite = (this.targetInput.getAttribute('data-selector-write-mode') === 'replace');
	};
	InlineSelector.prototype =
	{
		onKeyDown: function(container, e)
		{
			if (e.keyCode == 45 && e.altKey === false && e.ctrlKey === false && e.shiftKey === false)
			{
				this.openMenu(e);
				e.preventDefault();
			}
		},
		openMenu: function(e)
		{
			var me = this, i, menuItems = [];

			var fields = this.component.data['ENTITY_FIELDS'];
			for (i = 0; i < fields.length; ++i)
			{
				menuItems.push({
					text: fields[i]['Name'],
					field: fields[i],
					onclick: function(e, item)
					{
						this.popupWindow.close();
						me.onFieldSelect(item.field || item.options.field);
					}
				});
			}

			var menuId = this.menuButton.getAttribute('data-selector-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				this.menuButton.setAttribute('data-selector-id', menuId);
			}

			BX.PopupMenu.show(
				menuId,
				this.menuButton,
				menuItems,
				{
					zIndex: 200,
					autoHide: true,
					offsetLeft: (BX.pos(this.menuButton)['width'] / 2),
					angle: { position: 'top', offset: 0 },
					className: 'crm-automation-inline-selector-menu'
				}
			);
			this.menu = BX.PopupMenu.currentItem;
		},
		onFieldSelect: function(field)
		{
			if (this.replaceOnWrite)
			{
				this.targetInput.value = '{{' + field['Name'] + '}}';
				this.targetInput.selectionEnd = this.targetInput.value.length;
			}
			else
			{
				var beforePart = this.targetInput.value.substr(0, this.targetInput.selectionEnd),
					middlePart = '{{' + field['Name'] + '}}',
					afterPart = this.targetInput.value.substr(this.targetInput.selectionEnd);

				this.targetInput.value = beforePart + middlePart + afterPart;
				this.targetInput.selectionEnd = beforePart.length + middlePart.length;
			}

			BX.fireEvent(this.targetInput, 'change');
		},
		destroy: function()
		{
			if (this.menu)
				this.menu.popupWindow.close();
		}
	};
	// <- InlineSelector
	// -> InlineSelectorHtml
	var InlineSelectorHtml = function(component, targetNode)
	{
		var me = this;
		this.component = component;
		this.editorNode = targetNode.firstElementChild.firstElementChild;
		this.menuButton = BX.create('span', {
			attrs: {className: 'crm-automation-popup-select-dotted'},
			events: {
				click: BX.delegate(me.openMenu, this)
			}
		});
		targetNode.firstElementChild.appendChild(this.menuButton);
		this.bindEvents();
	};

	BX.extend(InlineSelectorHtml, InlineSelector);

	InlineSelectorHtml.prototype.getEditor = function()
	{
		var editor;
		if (this.editorNode)
		{
			var editorId = this.editorNode.id.split('-');
			editor = BXHtmlEditor.Get(editorId[editorId.length -1]);
		}
		return editor;
	};

	InlineSelectorHtml.prototype.bindEvents = function()
	{
		this.editorInitFunction = this.bindEditorHooks.bind(this);
		BX.addCustomEvent('OnEditorInitedAfter', this.editorInitFunction);
	};

	InlineSelectorHtml.prototype.unBindEvents = function()
	{
		BX.removeCustomEvent('OnEditorInitedAfter', this.editorInitFunction);
	};

	InlineSelectorHtml.prototype.bindEditorHooks = function(editor)
	{
		var header = '', footer = '';
		if (editor.dom.cont !== this.editorNode)
		{
			return false;
		}
		BX.addCustomEvent(editor, "OnParse", function(mode)
		{
			if (!mode)
			{
				var content = this.content;

				content = content.replace(/(^[\s\S]*?)(<body.*?>)/i, function(str){
						header = str;
						return '';
					}
				);

				content = content.replace(/(<\/body>[\s\S]*?$)/i,  function(str){
						footer = str;
						return '';
					}
				);

				this.content = content;
			}
		});

		BX.addCustomEvent(editor, "OnAfterParse", function(mode)
		{
			if (mode)
			{
				var content = this.content;

				content = content.replace(/^[\s\S]*?<body.*?>/i, "");
				content = content.replace(/<\/body>[\s\S]*?$/i, "");

				if (header !== '' && footer !== '')
				{
					content = header + content + footer;
				}


				this.content = content;
			}
		});
	};

	InlineSelectorHtml.prototype.onFieldSelect = function(field)
	{
		var insertText = '{{' + field['Name'] + '}}';
		var editor = this.getEditor();
		if (editor && editor.InsertHtml)
		{
			editor.InsertHtml(insertText);
		}
	};
	InlineSelectorHtml.prototype.destroy = function()
	{
		if (this.menu)
			this.menu.popupWindow.close();
		this.unBindEvents();
	};
	InlineSelectorHtml.prototype.onBeforeSave = function()
	{
		var editor = this.getEditor();
		if (editor && editor.SaveContent)
		{
			editor.SaveContent();
		}
	};
	InlineSelectorHtml.prototype.onPopupResize = function()
	{
		var editor = this.getEditor();
		if (editor && editor.ResizeSceleton)
		{
			editor.ResizeSceleton();
		}
	};
	// <- InlineSelectorHtml
	// -> TimeSelector
	var TimeSelector = function(targetInput)
	{
		this.targetInput = targetInput;

		var d = new Date(), currentValue = this.unFormatTime(targetInput.value);
		d.setHours(0, 0, 0, 0);
		d.setTime(d.getTime() + currentValue * 1000);
		targetInput.value = this.formatTime(d); //convert to site format on client side.

		BX.bind(targetInput, 'click', BX.delegate(this.showClock, this));
	};
	TimeSelector.prototype =
	{
		showClock: function (e)
		{
			if (!this.clockInstance)
			{
				this.clockInstance = new BX.CClockSelector({
					start_time: this.unFormatTime(this.targetInput.value),
					node: this.targetInput,
					callback: BX.delegate(this.onTimeSelect, this),
					zIndex: 200
				});
			}
			this.clockInstance.Show();
		},
		onTimeSelect: function(v)
		{
			this.targetInput.value = v;
			BX.fireEvent(this.targetInput, 'change');
			this.clockInstance.closeWnd();
		},
		unFormatTime: function(time)
		{
			var q = time.split(/[\s:]+/);
			if (q.length == 3)
			{
				var mt = q[2];
				if (mt == 'pm' && q[0] < 12)
					q[0] = parseInt(q[0], 10) + 12;

				if (mt == 'am' && q[0] == 12)
					q[0] = 0;

			}
			return parseInt(q[0], 10) * 3600 + parseInt(q[1], 10) * 60;
		},
		formatTime: function(date)
		{
			var dateFormat = BX.date.convertThurlyFormat(BX.message('FORMAT_DATE')).replace(/:?\s*s/, ''),
				timeFormat = BX.date.convertThurlyFormat(BX.message('FORMAT_DATETIME')).replace(/:?\s*s/, ''),
				str1 = BX.date.format(dateFormat, date),
				str2 = BX.date.format(timeFormat, date);
			return BX.util.trim(str2.replace(str1, ''));
		},
		destroy: function()
		{
			if (this.clockInstance)
				this.clockInstance.closeWnd();
		}
	};
	// <- TimeSelector
	// -> SaveStateCheckbox
	var SaveStateCheckbox = function(checkbox, robot)
	{
		this.checkbox = checkbox;
		this.robot = robot;
		this.needSync = robot.draft;
		if (this.needSync)
		{
			var key = this.getKey();
			var savedState = robot.component.getUserOption('save_state_checkboxes', key, 'N');
			if (savedState === 'Y')
			{
				checkbox.checked = true;
			}
		}
	};
	SaveStateCheckbox.prototype =
	{
		getKey: function()
		{
			return this.checkbox.getAttribute('data-save-state-key');
		},
		destroy: function()
		{
			if (this.needSync)
			{
				var key = this.getKey();
				var value = this.checkbox.checked? 'Y' : 'N';
				this.robot.component.setUserOption('save_state_checkboxes', key, value);
			}
		}
	};
	// <- SaveStateCheckbox
	// -> DelayIntervalSelector
	var DelayIntervalSelector = function(options)
	{
		this.basisFields = [];
		if (BX.type.isPlainObject(options))
		{
			this.labelNode = options.labelNode;
			this.typeNode = options.typeNode;
			this.basisNode = options.basisNode;
			this.valueNode = options.valueNode;
			this.valueTypeNode = options.valueTypeNode;
			this.workTimeNode = options.workTimeNode;

			if (BX.type.isArray(options.basisFields))
				this.basisFields = options.basisFields;
		}
	};
	DelayIntervalSelector.prototype =
	{
		init: function(delay)
		{
			this.delay = delay;
			this.setLabelText();
			this.bindLabelNode();
			this.prepareBasisFields();
		},
		setLabelText: function()
		{
			if (this.delay && this.labelNode)
			{
				this.labelNode.textContent = formatDelayInterval(
					this.delay,
					BX.message('CRM_AUTOMATION_CMP_AT_ONCE'),
					this.basisFields
				);
			}
		},
		bindLabelNode: function()
		{
			if (this.labelNode)
			{
				BX.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
			}
		},
		onLabelClick: function(e)
		{
			this.showDelayIntervalPopup();
			e.preventDefault();
		},
		showDelayIntervalPopup: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var form = BX.create("form", {
				attrs: { className: "crm-automation-popup-select-block" }
			});

			var radioNow = BX.create("input", {
				attrs: {
					className: "crm-automation-popup-select-input",
					id: uid + "now",
					type: "radio",
					value: 'now',
					name: "type"
				}
			});
			if (delay.isNow())
				radioNow.setAttribute('checked', 'checked');

			var labelNow = BX.create("label", {
				attrs: {
					className: "crm-automation-popup-select-wrapper",
					for: uid + "now"
				},
				children: [
					BX.create('span', {
						attrs: {className: 'crm-automation-popup-settings-title'},
						text: BX.message('CRM_AUTOMATION_CMP_AT_ONCE_2')
					})
				]
			});

			var labelNowHelpNode = BX.create('span', {
				attrs: {
					className: "crm-automation-status-help crm-automation-status-help-right",
					'data-text': BX.message('CRM_AUTOMATION_CMP_DELAY_NOW_HELP')
				},
				text: '?'
			});
			HelpHint.bindToNode(labelNowHelpNode);
			labelNow.appendChild(labelNowHelpNode);

			form.appendChild(BX.create("div", {
				attrs: { className: "crm-automation-popup-select-item" },
				children: [radioNow, labelNow]
			}));

			form.appendChild(this.createAfterControlNode());

			if (this.basisFields.length > 0)
			{
				form.appendChild(this.createBeforeControlNode());
				form.appendChild(this.createInControlNode());
			}

			var workTimeRadio = BX.create("input", {
				attrs: {
					type: "checkbox",
					id: uid + "worktime",
					name: "worktime",
					value: '1',
					style: 'vertical-align: middle'
				},
				props: {
					checked: delay.workTime
				}
			});

			var workTimeHelpNode = BX.create('span', {
				attrs: {
					className: "crm-automation-status-help crm-automation-status-help-right",
					'data-text': BX.message('CRM_AUTOMATION_CMP_DELAY_WORKTIME_HELP')
				},
				text: '?'
			});
			HelpHint.bindToNode(workTimeHelpNode);

			form.appendChild(BX.create("div", {
				attrs: { className: "crm-automation-popup-settings-title" },
				children: [
					workTimeRadio,
					BX.create("label", {
						attrs: {
							className: "crm-automation-popup-settings-lbl",
							for: uid + "worktime"
						},
						text: BX.message('CRM_AUTOMATION_CMP_WORK_TIME')
					}),
					workTimeHelpNode
				]
			}));

			var popup = new BX.PopupWindow('crm-automation-popup-set', this.labelNode, {
				autoHide: true,
				closeByEsc: true,
				closeIcon: false,
				titleBar: false,
				zIndex: 0,
				angle: true,
				offsetLeft: 20,
				content: form,
				buttons: [
					new BX.PopupWindowButton({
						text: BX.message('CRM_AUTOMATION_CMP_CHOOSE'),
						className: "webform-button webform-button-create crm-automation-button-left" ,
						events: {
							click: function(){
								var formData = BX.ajax.prepareForm(form);
								me.saveFormData(formData['data']);

								if (me.fieldsMenu)
									me.fieldsMenu.popupWindow.close();
								this.popupWindow.close();
							}}
					})
				],
				overlay: { backgroundColor: 'transparent' }
			});

			popup.show();
		},
		saveFormData: function(formData)
		{
			if (formData['type'] === 'now')
			{
				this.delay.setNow();
			}
			else if (formData['type'] === DelayInterval.Type.In)
			{
				this.delay.setType(DelayInterval.Type.In);
				this.delay.setValue(0);
				this.delay.setValueType('i');
				this.delay.setBasis(formData['basis_in']);
			}
			else
			{
				this.delay.setType(formData['type']);
				this.delay.setValue(formData['value_' + formData['type']]);
				this.delay.setValueType(formData['value_type_'+formData['type']]);

				if (formData['type'] === DelayInterval.Type.After)
					this.delay.setBasis(DelayInterval.Basis.CurrentDateTime);
				else
					this.delay.setBasis(formData['basis_before']);
			}

			this.delay.setWorkTime(formData['worktime']);

			this.setLabelText();
			this.updateValueNodes();
		},
		createAfterControlNode: function()
		{
			var delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioAfter = BX.create("input", {
				attrs: {
					className: "crm-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.After,
					name: "type"
				}
			});
			if (delay.type === DelayInterval.Type.After && delay.value > 0)
				radioAfter.setAttribute('checked', 'checked');

			var valueNode = BX.create('input', {
				attrs: {
					type: 'text',
					name: 'value_after',

					className: 'crm-automation-popup-settings-input'
				},
				props: {
					value: delay.type === DelayInterval.Type.After && delay.value ? delay.value : '5'
				}
			});

			var labelAfter = BX.create("label", {
				attrs: {
					className: "crm-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'crm-automation-popup-settings-title'},
						text: BX.message('CRM_AUTOMATION_CMP_THROUGH_2')
					}),
					valueNode,
					this.createValueTypeSelector('value_type_after')
				]
			});

			var afterHelpNode = BX.create('span', {
				attrs: {
					className: "crm-automation-status-help crm-automation-status-help-right",
					'data-text': BX.message('CRM_AUTOMATION_CMP_DELAY_AFTER_HELP')
				},
				text: '?'
			});
			HelpHint.bindToNode(afterHelpNode);
			labelAfter.appendChild(afterHelpNode);

			return BX.create("div", {
				attrs: { className: "crm-automation-popup-select-item" },
				children: [radioAfter, labelAfter]
			});
		},
		createBeforeControlNode: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioBefore = BX.create("input", {
				attrs: {
					className: "crm-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.Before,
					name: "type"
				}
			});

			if (delay.type === DelayInterval.Type.Before)
				radioBefore.setAttribute('checked', 'checked');

			var valueNode = BX.create('input', {
				attrs: {
					type: 'text',
					name: 'value_before',

					className: 'crm-automation-popup-settings-input'
				},
				props: {
					value: delay.type === DelayInterval.Type.Before && delay.value ? delay.value : '5'
				}
			});

			var labelBefore = BX.create("label", {
				attrs: {
					className: "crm-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'crm-automation-popup-settings-title'},
						text: BX.message('CRM_AUTOMATION_CMP_FOR_TIME_2')
					}),
					valueNode,
					this.createValueTypeSelector('value_type_before'),
					BX.create('span', {
						attrs: {className: 'crm-automation-popup-settings-title crm-automation-popup-settings-title-auto-width'},
						text: BX.message('CRM_AUTOMATION_CMP_BEFORE')
					})
				]
			});

			var basisField = this.getBasisField(delay.basis);
			var basisValue = delay.basis;
			if (!basisField)
			{
				basisField = this.basisFields[0];
				basisValue = basisField.SystemExpression;
			}

			var beforeBasisValueNode = BX.create('input', {
				attrs: {
					type: "hidden",
					name: "basis_before",
					value: basisValue
				}
			});

			var beforeBasisNode = BX.create('span', {
				attrs: {
					className: "crm-automation-popup-settings-link crm-automation-delay-interval-basis"
				},
				text: basisField ? basisField.Name : BX.message('CRM_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
				events: {
					click: function(e)
					{
						me.onBeforeBasisClick(e, this, function(field)
						{
							beforeBasisNode.textContent = field.Name;
							beforeBasisValueNode.value = field.SystemExpression;
						});
					}
				}
			});
			labelBefore.appendChild(beforeBasisValueNode);
			labelBefore.appendChild(beforeBasisNode);
			var beforeHelpNode = BX.create('span', {
				attrs: {
					className: "crm-automation-status-help crm-automation-status-help-right",
					'data-text': BX.message('CRM_AUTOMATION_CMP_DELAY_BEFORE_HELP')
				},
				text: '?'
			});
			HelpHint.bindToNode(beforeHelpNode);
			labelBefore.appendChild(beforeHelpNode);

			return BX.create("div", {
				attrs: {className: "crm-automation-popup-select-item"},
				children: [radioBefore, labelBefore]
			});
		},
		createInControlNode: function()
		{
			var me = this, delay = this.delay;
			var uid = Component.generateUniqueId();

			var radioIn = BX.create("input", {
				attrs: {
					className: "crm-automation-popup-select-input",
					id: uid,
					type: "radio",
					value: DelayInterval.Type.In,
					name: "type"
				}
			});

			if (delay.type === DelayInterval.Type.In)
				radioIn.setAttribute('checked', 'checked');


			var labelIn = BX.create("label", {
				attrs: {
					className: "crm-automation-popup-select-wrapper",
					for: uid
				},
				children: [
					BX.create('span', {
						attrs: {className: 'crm-automation-popup-settings-title'},
						text: BX.message('CRM_AUTOMATION_CMP_IN_TIME_2')
					})
				]
			});

			var basisField = this.getBasisField(delay.basis);
			var basisValue = delay.basis;
			if (!basisField)
			{
				basisField = this.basisFields[0];
				basisValue = basisField.SystemExpression;
			}

			var inBasisValueNode = BX.create('input', {
				attrs: {
					type: "hidden",
					name: "basis_in",
					value: basisValue
				}
			});

			var inBasisNode = BX.create('span', {
				attrs: {
					className: "crm-automation-popup-settings-link crm-automation-delay-interval-basis"
				},
				text: basisField ? basisField.Name : BX.message('CRM_AUTOMATION_CMP_CHOOSE_DATE_FIELD'),
				events: {
					click: function(e)
					{
						me.onBeforeBasisClick(e, this, function(field)
						{
							inBasisNode.textContent = field.Name;
							inBasisValueNode.value = field.SystemExpression;
						});
					}
				}
			});
			labelIn.appendChild(inBasisValueNode);
			labelIn.appendChild(inBasisNode);
			var helpNode = BX.create('span', {
				attrs: {
					className: "crm-automation-status-help crm-automation-status-help-right",
					'data-text': BX.message('CRM_AUTOMATION_CMP_DELAY_IN_HELP')
				},
				text: '?'
			});
			HelpHint.bindToNode(helpNode);
			labelIn.appendChild(helpNode);

			return BX.create("div", {
				attrs: {className: "crm-automation-popup-select-item"},
				children: [radioIn, labelIn]
			});
		},
		createValueTypeSelector: function(name)
		{
			var delay = this.delay;
			var labelTexts = {
				i: BX.message('CRM_AUTOMATION_CMP_INTERVAL_M'),
				h: BX.message('CRM_AUTOMATION_CMP_INTERVAL_H'),
				d: BX.message('CRM_AUTOMATION_CMP_INTERVAL_D')
			};

			var label = BX.create('label', {
				attrs: {className: 'crm-automation-popup-settings-link'},
				text: labelTexts[delay.valueType]

			});

			var input = BX.create('input', {
				attrs: {
					type: 'hidden',
					name: name
				},
				props: {
					value: delay.valueType
				}
			});

			BX.bind(label, 'click', this.onValueTypeSelectorClick.bind(this, label, input));


			return BX.create('span', {
				children: [label, input]
			})
		},
		onValueTypeSelectorClick: function(label, input)
		{
			var uid = Component.generateUniqueId();

			var handler = function(e, item)
			{
				this.popupWindow.close();
				input.value = item.valueId;
				label.textContent = item.text;
			};

			var menuItems = [
				{
					text: BX.message('CRM_AUTOMATION_CMP_INTERVAL_M'),
					valueId: 'i',
					onclick: handler
				},{
					text: BX.message('CRM_AUTOMATION_CMP_INTERVAL_H'),
					valueId: 'h',
					onclick: handler
				},{
					text: BX.message('CRM_AUTOMATION_CMP_INTERVAL_D'),
					valueId: 'd',
					onclick: handler
				}
			];

			BX.PopupMenu.show(
				uid,
				label,
				menuItems,
				{
					autoHide: true,
					offsetLeft: 25,
					angle: { position: 'top'},
					zIndex: 200,
					events: {
						onPopupClose: function ()
						{
							this.destroy();
						}
					}
				}
			);
		},
		onBeforeBasisClick: function(e, labelNode, callback)
		{
			var me = this, i, menuItems = [];
			for (i = 0; i < this.basisFields.length; ++i)
			{
				menuItems.push({
					text: this.basisFields[i].Name,
					field: this.basisFields[i],
					onclick: function(e, item)
					{
						if (callback)
							callback(item.field || item.options.field);

						this.popupWindow.close();
					}
				});
			}

			var menuId = labelNode.getAttribute('data-menu-id');
			if (!menuId)
			{
				menuId = Component.generateUniqueId();
				labelNode.setAttribute('data-menu-id', menuId);
			}

			BX.PopupMenu.show(
				menuId,
				labelNode,
				menuItems,
				{
					zIndex: 200,
					autoHide: true,
					offsetLeft: (BX.pos(labelNode)['width'] / 2),
					angle: { position: 'top', offset: 0 }
				}
			);

			this.fieldsMenu = BX.PopupMenu.currentItem;
		},
		updateValueNodes: function()
		{
			if (this.delay)
			{
				if (this.typeNode)
					this.typeNode.value = this.delay.type;
				if (this.valueNode)
					this.valueNode.value = this.delay.value;
				if (this.valueTypeNode)
					this.valueTypeNode.value = this.delay.valueType;
				if (this.basisNode)
					this.basisNode.value = this.delay.basis;
				if (this.workTimeNode)
					this.workTimeNode.value = this.delay.workTime ? 1 : 0;
			}
		},
		getBasisField: function(basis)
		{
			var field = null;
			for (var i = 0; i < this.basisFields.length; ++i)
			{
				if (basis === this.basisFields[i].SystemExpression)
					field = this.basisFields[i];
			}
			return field;
		},
		prepareBasisFields: function()
		{
			var i, fld, fields = [];
			for (i = 0; i < this.basisFields.length; ++i)
			{
				fld = this.basisFields[i];
				if (
					fld['Id'] !== 'DATE_CREATE'
					&& fld['Id'] !== 'DATE_MODIFY'
					&& fld['Id'] !== 'EVENT_DATE'
					&& fld['Id'] !== 'BIRTHDATE'
				)
					fields.push(fld);
			}
			this.basisFields = fields;
		}
	};
	// <- DelayIntervalSelector
	// -> ConditionSelector
	var ConditionSelector = function(options)
	{
		this.fields = [];
		if (BX.type.isPlainObject(options))
		{
			this.labelNode = options.labelNode;
			this.fieldNode = options.fieldNode;
			this.conditionNode = options.conditionNode;
			this.valueNode = options.valueNode;

			if (BX.type.isArray(options.fields))
				this.fields = options.fields;
		}
	};
	ConditionSelector.prototype =
		{
			init: function(condition)
			{
				this.condition = condition;
				this.setLabelText();
				this.bindLabelNode();
			},
			setLabelText: function()
			{
				if (!this.labelNode || !this.condition)
					return;

				BX.cleanNode(this.labelNode);

				if (this.condition.field !== '')
				{
					var field = this.getField(this.condition.field) || '?';
					var valueLabel = this.condition.value;
					if (valueLabel && field['Type'] == 'bool')
					{
						valueLabel = BX.message(this.condition.value === 'Y' ? 'CRM_AUTOMATION_YES' : 'CRM_AUTOMATION_NO');
					}
					else if (valueLabel && field['Type'] == 'select' && field['Options'][this.condition.value])
					{
						valueLabel = field['Options'][this.condition.value];
					}

					this.labelNode.appendChild(BX.create("span", {
						attrs: {
							className: "crm-automation-popup-settings-link"
						},
						text: field.Name
					}));
					this.labelNode.appendChild(BX.create("span", {
						attrs: {
							className: "crm-automation-popup-settings-link"
						},
						text: this.getConditionLabel(this.condition.condition)
					}));
					if (valueLabel)
					{
						this.labelNode.appendChild(BX.create("span", {
							attrs: {
								className: "crm-automation-popup-settings-link"
							},
							text: valueLabel
						}));
					}
					this.labelNode.appendChild(BX.create("span", {
						attrs: {
							className: "crm-automation-popup-settings-link-remove"
						},
						events: {
							click: this.removeCondition.bind(this)
						}
					}));
				}
				else
				{
					this.labelNode.appendChild(BX.create("span", {
						attrs: {
							className: "crm-automation-popup-settings-link"
						},
						text: BX.message('CRM_AUTOMATION_ROBOT_CONDITION_EMPTY')
					}));
				}
			},
			bindLabelNode: function()
			{
				if (this.labelNode)
				{
					BX.bind(this.labelNode, 'click', BX.delegate(this.onLabelClick, this));
				}
			},
			onLabelClick: function(e)
			{
				this.showPopup();
			},
			showPopup: function()
			{
				var me = this, fields = this.filterFields();
				var fieldSelect = BX.create('select', {
					attrs: {className: 'crm-automation-popup-settings-dropdown'}
				});

				for (var i = 0; i < fields.length; ++i)
				{
					fieldSelect.appendChild(BX.create('option', {
						props: {value: fields[i]['Id']},
						text: fields[i]['Name']
					}));
				}

				var selectedField = this.getField(this.condition.field);
				if (!selectedField)
					selectedField = fields[0];

				var conditionInput = (this.condition.condition.indexOf('empty') < 0)
					? this.createValueNode(selectedField) : null;

				var valueWrapper = BX.create('div', {attrs: {className: 'crm-automation-popup-settings'},
					children: [conditionInput]
				});

				var conditionSelect = this.createConditionNode(selectedField, valueWrapper);
				var conditionWrapper = BX.create('div', {attrs: {className: 'crm-automation-popup-settings'},
					children: [conditionSelect]
				});

				if (this.condition.field !== '')
				{
					fieldSelect.value = this.condition.field;
					conditionSelect.value = this.condition.condition;
					if (conditionInput)
						conditionInput.value = this.condition.value;
				}

				var form = BX.create("form", {
					attrs: { className: "crm-automation-popup-select-block" },
					children: [
						BX.create('div', {attrs: {className: 'crm-automation-popup-settings'},
							children: [fieldSelect]
						}),
						conditionWrapper,
						valueWrapper
					]
				});

				BX.bind(fieldSelect, 'change', this.onFieldChange.bind(
					this,
					fieldSelect,
					conditionWrapper,
					valueWrapper
				));

				var popup = new BX.PopupWindow('crm-automation-popup-set', this.labelNode, {
					className: 'crm-automation-popup-set',
					autoHide: true,
					closeByEsc: true,
					closeIcon: false,
					titleBar: false,
					zIndex: 0,
					angle: true,
					offsetLeft: 45,
					content: form,
					buttons: [
						new BX.PopupWindowButton({
							text: BX.message('CRM_AUTOMATION_CMP_CHOOSE'),
							className: "webform-button webform-button-create" ,
							events: {
								click: function(){
									me.condition.setField(fieldSelect.value);
									me.condition.setCondition(conditionWrapper.firstChild.value);

									if (valueWrapper.firstChild)
									{
										me.condition.setValue(valueWrapper.firstChild.value);
									}
									else
									{
										me.condition.setValue('');
									}

									me.setLabelText();
									me.updateValueNodes();
									this.popupWindow.close();
								}}
						})
					],
					overlay: { backgroundColor: 'transparent' }
				});

				popup.show()
			},
			updateValueNodes: function()
			{
				if (this.condition)
				{
					if (this.fieldNode)
						this.fieldNode.value = this.condition.field;
					if (this.conditionNode)
						this.conditionNode.value = this.condition.condition;
					if (this.valueNode)
						this.valueNode.value = this.condition.value;
				}
			},
			/**
			 * @param {Node} selectNode
			 * @param {Node} conditionWrapper
			 * @param {Node} valueWrapper
			 */
			onFieldChange: function(selectNode, conditionWrapper, valueWrapper)
			{
				var field = this.getField(selectNode.value);
				var conditionNode = this.createConditionNode(field, valueWrapper);
				conditionWrapper.replaceChild(conditionNode, conditionWrapper.firstChild);
				this.onConditionChange(conditionNode, field, valueWrapper);
			},
			/**
			 * @param {Node} selectNode
			 * @param {Object} field
			 * @param {Node} valueWrapper
			 */
			onConditionChange: function(selectNode, field, valueWrapper)
			{
				BX.cleanNode(valueWrapper);

				if (selectNode.value.indexOf('empty') < 0)
				{
					var valueNode = this.createValueNode(field);
					valueWrapper.appendChild(valueNode);
				}
			},
			getField: function(id)
			{
				var field = null;
				for (var i = 0; i < this.fields.length; ++i)
				{
					if (id == this.fields[i].Id)
						field = this.fields[i];
				}
				return field;
			},
			getConditions: function(fieldType)
			{
				var list;
				switch (fieldType)
				{
					case 'bool':
					case 'select':
						list = {
							'!empty': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
							'empty': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_EMPTY'),
							'=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_EQ'),
							'!=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_NE')
						};
						break;
					default:
						list = {
							'!empty': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_NOT_EMPTY'),
							'empty': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_EMPTY'),
							'=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_EQ'),
							'>': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_GT'),
							'>=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_GTE'),
							'<': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_LT'),
							'<=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_LTE'),
							'!=': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_NE'),
							'in': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_IN'),
							'contain': BX.message('CRM_AUTOMATION_ROBOT_CONDITION_CONTAIN')
						};
				}

				return list;
			},
			getConditionLabel: function(id)
			{
				return this.getConditions()[id];
			},
			filterFields: function()
			{
				var i, type, filtered = [];
				for (i = 0; i < this.fields.length; ++i)
				{
					type = this.fields[i]['Type'];
					if (
						type == 'bool'
						// || type == 'date'
						// || type == 'datetime' TODO: add relative date selector (today, tomorrow etc.)
						|| type == 'double'
						|| type == 'int'
						|| type == 'select'
						|| type == 'string'
						|| type == 'text'
					)
					{
						filtered.push(this.fields[i]);
					}
				}
				return filtered;
			},
			createValueNode: function(field)
			{
				var node;

				switch (field['Type'])
				{
					case 'bool':
						node = BX.create('select', {
							attrs: {className: 'crm-automation-popup-settings-dropdown'},
							children: [
								BX.create('option', {
									props: {value: 'Y'},
									text: BX.message('CRM_AUTOMATION_YES')
								}),
								BX.create('option', {
									props: {value: 'N'},
									text: BX.message('CRM_AUTOMATION_NO')
								})
							]
						});
						break;

					case 'select':
						node = BX.create('select', {
							attrs: {className: 'crm-automation-popup-settings-dropdown'}
						});

						var options = field['Options'];
						for (var optionId in options)
						{
							if (!options.hasOwnProperty(optionId))
								continue;
							node.appendChild(BX.create('option', {
								props: {value: optionId},
								text: options[optionId]
							}));
						}
						break;

					default:
						node = BX.create("input", {
							attrs: {
								className: 'crm-automation-popup-input',
								type: "text"
							}
						});
				}

				return node;
			},
			createConditionNode: function(field, valueWrapper)
			{
				var select = BX.create('select', {
					attrs: {className: 'crm-automation-popup-settings-dropdown'}
				});

				var conditionList = this.getConditions(field['Type']);
				for (var conditionId in conditionList)
				{
					if (!conditionList.hasOwnProperty(conditionId))
						continue;
					select.appendChild(BX.create('option', {
						props: {value: conditionId},
						text: conditionList[conditionId]
					}));
				}

				BX.bind(select, 'change', this.onConditionChange.bind(
					this,
					select,
					field,
					valueWrapper
				));

				return select;
			},
			/**
			 * @param {Event} e
			 */
			removeCondition: function(e)
			{
				this.condition = new Condition();
				this.updateValueNodes();
				this.setLabelText();

				e.stopPropagation();
			}
		};
	// <- ConditionSelector
	var Runtime = {
		setRobotSettingsDialog: function(dialog)
		{
			this.robotSettingsDialog = dialog;
		},
		getRobotSettingsDialog: function()
		{
			return this.robotSettingsDialog;
		}
	};

	//Private helpers
	var DelayInterval = function (params)
	{
		this.basis = DelayInterval.Basis.CurrentDateTime;
		this.type = DelayInterval.Type.After;
		this.value = 0;
		this.valueType = 'i';
		this.workTime = false;

		if (BX.type.isPlainObject(params))
		{
			if (params['type'])
				this.setType(params['type']);
			if (params['value'])
				this.setValue(params['value']);
			if (params['valueType'])
				this.setValueType(params['valueType']);
			if (params['basis'])
				this.setBasis(params['basis']);
			if (params['workTime'])
				this.setWorkTime(params['workTime']);
		}
	};

	DelayInterval.Type = {
		After: 'after',
		Before: 'before',
		In: 'in'
	};

	DelayInterval.Basis = {
		CurrentDate: '{=System:Date}',
		CurrentDateTime: '{=System:Now}'
	};

	DelayInterval.prototype = {
		setType: function(type)
		{
			if (
				type !== DelayInterval.Type.After
				&& type !== DelayInterval.Type.Before
				&& type !== DelayInterval.Type.In
			)
			{
				type = DelayInterval.Type.After;
			}
			this.type = type;
		},
		setValue: function(value)
		{
			value = parseInt(value);
			this.value = value >= 0 ? value : 0;
		},
		setValueType: function(valueType)
		{
			if (valueType !== 'i' && valueType !== 'h' && valueType !== 'd')
				valueType = 'i';

			this.valueType = valueType;
		},
		setBasis: function(basis)
		{
			if (BX.type.isNotEmptyString(basis))
				this.basis = basis;
		},
		setWorkTime: function(flag)
		{
			this.workTime = !!flag;
		},
		isNow: function()
		{
			return (
				this.type === DelayInterval.Type.After
				&& this.basis === DelayInterval.Basis.CurrentDateTime
				&& !this.value
			);
		},
		setNow: function()
		{
			this.setType(DelayInterval.Type.After);
			this.setValue(0);
			this.setValueType('i');
			this.setBasis(DelayInterval.Basis.CurrentDateTime);
		},
		serialize: function()
		{
			return {
				type: this.type,
				value: this.value,
				valueType: this.valueType,
				basis: this.basis,
				workTime: this.workTime ? 1 : 0
			}
		}
	};

	var Condition = function (params)
	{
		this.type = Condition.Type.Field;
		this.field = '';
		this.condition = '!empty';
		this.value = '';

		if (BX.type.isPlainObject(params))
		{
			if (params['field'])
				this.setField(params['field']);
			if (params['condition'])
				this.setCondition(params['condition']);
			if ('value' in params)
				this.setValue(params['value']);
		}
	};

	Condition.Type = {
		Field: 'field'
	};

	Condition.prototype = {
		setField: function(field)
		{
			if (BX.type.isNotEmptyString(field))
				this.field = field;
		},
		setCondition: function(condition)
		{
			if (!condition)
				condition = '=';
			this.condition = condition;
		},
		setValue: function(value)
		{
			this.value = value;
			if (this.condition === '=' && this.value === '')
				this.condition = 'empty';
			else if (this.condition === '!=' && this.value === '')
				this.condition = '!empty';
		},
		serialize: function()
		{
			var condition = this.condition;
			var value = this.value;

			if (this.condition === 'empty')
			{
				condition = '=';
				value = '';
			}
			else if (this.condition === '!empty')
			{
				condition = '!=';
				value = '';
			}

			return {
				type: this.type,
				field: this.field,
				condition: condition,
				value: value
			}
		}
	};

	var formatDelayInterval = function(delay, emptyText, fields)
	{
		var str = emptyText, prefix;

		if (delay.type == DelayInterval.Type.In)
		{
			str = BX.message('CRM_AUTOMATION_CMP_IN_TIME');
			if (BX.type.isArray(fields))
			{
				for (var i = 0; i < fields.length; ++i)
				{
					if (delay.basis == fields[i].SystemExpression)
					{
						str += ' ' + fields[i].Name;
						break;
					}
				}
			}

		}
		else if (delay.value)
		{
			prefix = delay.type == DelayInterval.Type.After ?
				BX.message('CRM_AUTOMATION_CMP_THROUGH') : BX.message('CRM_AUTOMATION_CMP_FOR_TIME');

			str = prefix + ' ' + getFormattedPeriodLabel(delay.value, delay.valueType);

			if (BX.type.isArray(fields))
			{
				for (var i = 0; i < fields.length; ++i)
				{
					if (delay.basis == fields[i].SystemExpression)
					{
						str += ' ' + BX.message('CRM_AUTOMATION_CMP_BEFORE') + ' ' + fields[i].Name;
						break;
					}
				}
			}
		}

		if (delay.workTime)
		{
			str += ', ' + BX.message('CRM_AUTOMATION_CMP_IN_WORKTIME');
		}

		return str;
	};

	var getPeriodLabels = function(period)
	{
		var labels = [];
		if (period === 'i')
			labels = [
				BX.message('CRM_AUTOMATION_CMP_MIN1'),
				BX.message('CRM_AUTOMATION_CMP_MIN2'),
				BX.message('CRM_AUTOMATION_CMP_MIN3')
			];
		else if (period === 'h')
			labels = [
				BX.message('CRM_AUTOMATION_CMP_HOUR1'),
				BX.message('CRM_AUTOMATION_CMP_HOUR2'),
				BX.message('CRM_AUTOMATION_CMP_HOUR3')
			];
		else if (period === 'd')
			labels = [
				BX.message('CRM_AUTOMATION_CMP_DAY1'),
				BX.message('CRM_AUTOMATION_CMP_DAY2'),
				BX.message('CRM_AUTOMATION_CMP_DAY3')
			];

		return labels;
	};

	var getFormattedPeriodLabel = function(value, type)
	{
		var label = value + ' ';
		var labelIndex = 0;
		if (value > 20)
			value = (value % 10);

		if (value == 1)
			labelIndex = 0;
		else if (value > 1 && value < 5)
			labelIndex = 1;
		else
			labelIndex = 2;

		var labels = getPeriodLabels(type);
		return label + (labels ? labels[labelIndex] : '');
	};

	var HelpHint = {
		popupHint: null,

		bindToNode: function(node)
		{
			BX.bind(node, 'mouseover', BX.proxy(function(){
				this.showHint(BX.proxy_context);
			}, this));
			BX.bind(node, 'mouseout', BX.delegate(this.hideHint, this));
		},
		showHint: function(node)
		{
			var rawText = node.getAttribute('data-text');
			if (!rawText)
				return;
			var text = BX.util.htmlspecialchars(rawText);
			text = BX.util.nl2br(text);
			if (!BX.type.isNotEmptyString(text))
				return;

			this.popupHint = new BX.PopupWindow('crm-automation-help-tip', node, {
				lightShadow: true,
				autoHide: false,
				darkMode: true,
				offsetLeft: 0,
				offsetTop: 2,
				bindOptions: {position: "top"},
				zIndex: 1100,
				events : {
					onPopupClose : function() {this.destroy()}
				},
				content : BX.create("div", { attrs : { style : "padding-right: 5px; width: 250px;" }, html: text})
			});
			this.popupHint.setAngle({offset:32, position: 'bottom'});
			this.popupHint.show();

			return true;
		},
		hideHint: function()
		{
			if (this.popupHint)
				this.popupHint.close();
			this.popupHint = null;
		}
	};

	return {
		Component: Component,
		TriggerManager: TriggerManager,
		TemplateManager: TemplateManager,
		Template: Template,
		Robot: Robot,
		Trigger: Trigger,
		Tracker: Tracker,
		Runtime: Runtime
	};
})(window.BX || window.top.BX);