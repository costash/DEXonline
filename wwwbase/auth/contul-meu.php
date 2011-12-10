<?php
require_once("../../phplib/util.php");
require_once("../../phplib/userPreferences.php");
util_assertNotMirror();

$sendButton = util_getRequestParameter('send');
$detailsVisible = util_getRequestParameter('detailsVisible');
$userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
$skin = util_getRequestParameter('skin');

$user = session_getUser();
if (!$user) {
  util_redirect('auth/login');
}

if ($sendButton) {
  $user->detailsVisible = $detailsVisible ? 1 : 0;
  $user->preferences = $userPrefs;
  if (session_isValidSkin($skin)) {
    $user->skin = $skin;
  }
  $user->save();
  session_setVariable('user', $user);
  FlashMessage::add('Informațiile au fost salvate.', 'info');
} else {
  $detailsVisible = $user->detailsVisible;
  $skin = session_getSkin();
  $userPrefs = $user->preferences;
}

foreach (preg_split('/,/', $userPrefs) as $pref) {
  if (isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

smarty_assign('detailsVisible', $detailsVisible);
smarty_assign('userPrefs', $userPreferencesSet);
smarty_assign('skin', $skin);
smarty_assign('availableSkins', pref_getServerPreference('skins'));
smarty_assign('privilegeNames', $PRIV_NAMES);
smarty_assign('page_title', 'Contul meu');
smarty_displayCommonPageWithSkin('auth/contul-meu.ihtml');
?>
