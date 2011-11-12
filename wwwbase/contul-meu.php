<?php
require_once("../phplib/util.php");
require_once("../phplib/userPreferences.php");
util_assertNotMirror();

$sendButton = util_getRequestParameter('send');
$nick = util_getRequestParameter('nick');
$newPass = util_getRequestParameter('newPass');
$newPass2 = util_getRequestParameter('newPass2');
$curPass = util_getRequestParameter('curPass');
$name = util_getRequestParameter('name');
$email = util_getRequestParameter('email');
$emailVisible = util_getRequestParameter('emailVisible');
$userPrefs = util_getRequestCheckboxArray('userPrefs', ',');
$skin = util_getRequestParameter('skin');

$user = session_getUser();
if (!$user) {
  util_redirect('login');
}

if ($sendButton) {
  // First, a few syntactic checks
  $error = true;
  if ($nick == "") {
    FlashMessage::add('Trebuie să vă alegeți un nume de cont.');
  } else if (strlen($nick) < 3) {
    FlashMessage::add('Numele de cont trebuie să aibă minim 3 caractere.');
  } else if ($nick != preg_replace("/[^a-zA-Z0-9_-]/", "", $nick)) {
    FlashMessage::add('Numele de cont poate conține numai caracterele indicate.');
  } else if (strlen($newPass) > 0 && strlen($newPass) < 4) {
    FlashMessage::add('Trebuie să vă alegeți o parolă de minim 4 caractere.');
  } else if ($newPass != $newPass2) {
    FlashMessage::add('Parolele nu coincid.');
  } else if ($email == "") {
    FlashMessage::add('Trebuie să precizați noua adresă de email.');
  } else if (!strstr($email, '.') || !strstr($email, '@')) {
    FlashMessage::add('Adresa de email nu este validă.');
  } else if (md5($curPass) != $user->password) {
    FlashMessage::add('Parola actuală este incorectă.');
  } else {
    $error = false;
  }

  // Verify that the email address and nickname are unique..
  if (!$error) {
    $userByNick = User::get_by_nick($nick);
    if ($userByNick && $userByNick->id != $user->id) {
      $error = true;
      FlashMessage::add('Acest nume de cont este deja folosit.');
    }
  }
  
  if (!$error) {
    $userByEmail = User::get_by_email($email);
    if ($userByEmail && $userByEmail->id != $user->id) {
      $error = true;
      FlashMessage::add('Această adresă de email este deja folosită.');
    }
  }
  
  // Things are swell, edit account and display acknowledgement
  if (!$error) {
    $user->nick = $nick;
    $user->name = $name;
    $user->email = $email;
    $user->emailVisible = $emailVisible ? 1 : 0;
    if ($newPass) {
      $user->password = md5($newPass);
    }
    $user->preferences = $userPrefs;
    if (session_isValidSkin($skin)) {
      $user->skin = $skin;
    }
    $user->save();
    session_setVariable('user', $user);
    FlashMessage::add('Informațiile au fost salvate.', 'info');
  }
} else {
  $nick = $user->nick;
  $newPass = '';
  $newPass2 = '';
  $curPass = '';
  $name = $user->name;
  $email = $user->email;
  $emailVisible = $user->emailVisible;
  $skin = session_getSkin();
  $userPrefs = $user->preferences;
}

foreach (preg_split('/,/', $userPrefs) as $pref) {
  if (isset($userPreferencesSet[$pref]) ) {
    $userPreferencesSet[$pref]['checked'] = true;
  }
}

smarty_assign('nick', $nick);
smarty_assign('newPass', $newPass);
smarty_assign('newPass2', $newPass2);
smarty_assign('curPass', $curPass);
smarty_assign('name', $name);
smarty_assign('email', $email);
smarty_assign('emailVisible', $emailVisible);
smarty_assign('userPrefs', $userPreferencesSet);
smarty_assign('skin', $skin);
smarty_assign('availableSkins', pref_getServerPreference('skins'));
smarty_assign('privilegeNames', $PRIV_NAMES);
smarty_assign('page_title', 'Contul meu');
smarty_displayCommonPageWithSkin('contul-meu.ihtml');
?>
