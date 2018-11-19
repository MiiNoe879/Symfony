{"version":3,"sources":["script.js"],"names":["BX","CrmEntityLiveFeedActivityList","this","_prefix","_activityEditor","_items","prototype","initialize","id","settings","_id","_settings","getSetting","activityEditorId","type","isNotEmptyString","CrmActivityEditor","items","activityWrapper","_resolveElement","data","i","length","itemData","itemId","parseInt","activityContainer","findChild","attribute","data-entity-id","toString","CrmEntityLiveFeedActivity","create","activityEditor","container","clientTemplate","referenceTemplate","params","CrmParamBag","name","defaultVal","hasOwnProperty","setSetting","val","elementId","self","_container","_completeButton","_subjectElem","_timeElem","_responsibleElem","_params","_enableExternalChange","addActivityChangeHandler","delegate","_onExternalChange","className","bind","_onCompleteButtonClick","_onTitleClick","_bindingElem","getId","isCompleted","getBooleanParam","setCompleted","completed","setActivityCompleted","_onComplete","layout","classOnly","typeId","getIntParam","direction","containerClassName","CrmActivityType","call","CrmActivityDirection","incoming","meeting","task","removeClass","addClass","now","Date","time","parseDate","getParam","innerHTML","util","htmlspecialchars","checked","trimDateTimeString","date","format","getDateTimeFormat","clientTitle","clientInfo","replace","ownerType","ownerTitle","referenceInfo","bindingHtml","e","isBoolean","setParam","viewActivity","PreventDefault","sender","action","isArray","comms","comm","entityType"],"mappings":"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","file":""}