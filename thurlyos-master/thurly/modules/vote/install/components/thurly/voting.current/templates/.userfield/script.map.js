{"version":3,"file":"script.min.js","sources":["script.js"],"names":["window","BX","Vote","d","prototype","repo","init","params","setTimeout","hasAttribute","setAttribute","BVotedUser","CID","controller","urlTemplate","nameTemplate","url","voteId","startCheck","bind","e","voteAJAX","this","href","VOTE_ID","stopVoting","PreventDefault","resumeVoting","view_form","addClass","form","action","ajax","prepareForm","data","link","VCLinkShowWait","addCustomEvent","res","findParent","className","VCLinkCloseWait","hide","send","el","removeClass","disabled","replace","thurly_sessid","method","processData","onsuccess","result","ob","processHTML","innerHTML","HTML","indexOf","defer","processScripts","SCRIPT","__destruct","dateTemplate","popup","parseInt","status","__construct","findChildren","tagName","ii","f","delegate","get","hasOwnProperty","onPullEvent","command","hasClass","changeData","unbindAll","removeCustomEvent","node","proxy_context","timeoutOver","clearTimeout","type","proxy","popupContainer","timeoutOut","close","getID","Date","getTime","make","needToCheckData","res1","contentContainer","i","isNew","create","props","children","findChild","items","avatarNode","tag","attr","id","length","attrs","src","appendChild","target","text","html","removeChild","firstChild","adjustWindow","popupScroll","show","nodeID","PopupWindow","lightShadow","offsetTop","offsetLeft","autoHide","closeByEsc","bindOptions","position","events","onPopupClose","destroy","onPopupDestroy","content","setAngle","forceBindPosition","adjustPosition","popupScrollCheck","scrollTop","scrollHeight","offsetHeight","unbind","bindElement","getAttribute","dataType","ID","answer_id","request_id","iNumPage","URL_TEMPLATE","NAME_TEMPLATE","sessid","StatusPage","push","onfailure","AJAX_RESULT","view_result","onCustomEvent","question","answer","q","per","isNaN","adjust","rel","rev","inumpage","style","width"],"mappings":"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"}