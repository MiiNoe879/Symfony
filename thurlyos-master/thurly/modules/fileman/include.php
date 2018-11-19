<?
/*patchlimitationmutatormark1*/
CModule::AddAutoloadClasses(
	"fileman",
	array(
		"CLightHTMLEditor" => "classes/general/light_editor.php",
		"CEditorUtils" => "classes/general/editor_utils.php",
		"CMedialib" => "classes/general/medialib.php",
		"CEventFileman" => "classes/general/fileman_event_list.php",
		"CCodeEditor" => "classes/general/code_editor.php",
		"CFileInput" => "classes/general/file_input.php",
		"CMedialibTabControl" => "classes/general/medialib.php",
		"CSticker" => "classes/general/sticker.php",
		"CSnippets" => "classes/general/snippets.php",
		"CAdminContextMenuML" => "classes/general/medialib_admin.php",
		"CHTMLEditor" => "classes/general/html_editor.php",
		"CComponentParamsManager" => "classes/general/component_params_manager.php",
		"CSpellchecker" => "classes/general/spellchecker.php"
	)
);

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/fileman/lang.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/main/admin_tools.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/fileman/fileman.php");
include_once($_SERVER["DOCUMENT_ROOT"]."/thurly/modules/fileman/properties.php");
/*patchlimitationmutatormark2*/

CJSCore::RegisterExt('file_input', array(
	'js' => '/thurly/js/fileman/core_file_input.js',
	'lang' => '/thurly/modules/fileman/lang/'.LANGUAGE_ID.'/classes/general/file_input.php'
));

CJSCore::RegisterExt('map_google', array(
	'js' => '/thurly/js/fileman/core_map_google.js'
));

CJSCore::RegisterExt('google_loader', array(
	'js' => '/thurly/js/fileman/google/loader.js',
	'oninit' => function()
	{
		$additionalLang = array(
			'GOOGLE_MAP_API_KEY' => \Thurly\Fileman\UserField\Address::getApiKey(),
			'GOOGLE_MAP_API_KEY_HINT' => \Thurly\Fileman\UserField\Address::getApiKeyHint(),
		);

		$trialHint = \Thurly\Fileman\UserField\Address::getTrialHint();
		if(is_array($trialHint))
		{
			$additionalLang['GOOGLE_MAP_TRIAL_TITLE'] = $trialHint[0];
			$additionalLang['GOOGLE_MAP_TRIAL'] = $trialHint[1];
		}

		return array(
			'lang_additional' => $additionalLang,
		);
	}
));

CJSCore::RegisterExt('google_map', array(
	'js' => '/thurly/js/fileman/google/map.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('google_geocoder', array(
	'js' => '/thurly/js/fileman/google/geocoder.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('google_autocomplete', array(
	'js' => '/thurly/js/fileman/google/autocomplete.js',
	'rel' => array('google_loader'),
));

CJSCore::RegisterExt('userfield_address', array(
	'js' => array('/thurly/js/fileman/userfield/address.js'),
	'css' => array('/thurly/js/fileman/userfield/address.css'),
	'lang' => '/thurly/modules/fileman/lang/'.LANGUAGE_ID.'/js_userfield_address.php',
	'rel' => array('google_map', 'google_geocoder', 'google_autocomplete', 'popup'),
));

//on update method still not exist
if(method_exists($GLOBALS["APPLICATION"], 'AddJSKernelInfo'))
{
	$GLOBALS["APPLICATION"]->AddJSKernelInfo(
		'fileman',
		array(
			'/thurly/js/fileman/light_editor/le_dialogs.js', '/thurly/js/fileman/light_editor/le_controls.js',
			'/thurly/js/fileman/light_editor/le_toolbarbuttons.js', '/thurly/js/fileman/light_editor/le_core.js'
		)
	);

	$GLOBALS["APPLICATION"]->AddCSSKernelInfo('fileman',array('/thurly/js/fileman/light_editor/light_editor.css'));

	// Park new html-editor
	$GLOBALS["APPLICATION"]->AddJSKernelInfo(
		'htmleditor',
		array(
			'/thurly/js/fileman/html_editor/range.js',
			'/thurly/js/fileman/html_editor/html-actions.js',
			'/thurly/js/fileman/html_editor/html-views.js',
			'/thurly/js/fileman/html_editor/html-parser.js',
			'/thurly/js/fileman/html_editor/html-base-controls.js',
			'/thurly/js/fileman/html_editor/html-controls.js',
			'/thurly/js/fileman/html_editor/html-components.js',
			'/thurly/js/fileman/html_editor/html-snippets.js',
			'/thurly/js/fileman/html_editor/html-editor.js'
		)
	);
}
