<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * English strings for jitsi
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_jitsi
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Jitsi';
$string['modulenameplural'] = 'Jitsis';
$string['modulename_help'] = 'Use the Jitsi module for videoconference. These videoconferences will use your Moodle username by displaying your username and avatar in videoconferences.

Jitsi-meet is an open-source videoconferencing solution that enables you to easily build and implement secure video conferencing solutions.';
$string['jitsi:addinstance'] = 'Add a new Jitsi';
$string['jitsi:moderation'] = 'Jitsi Moderation';
$string['jitsi:record'] = 'Record session';
$string['jitsi:createlink'] = 'Create link to invite to session';
$string['jitsi:sharedesktop'] = 'Share Desktop';
$string['jitsi:view'] = 'View Jitsi';
$string['jitsiname'] = 'Session name';
$string['jitsi'] = 'Jitsi';
$string['pluginadministration'] = 'Jitsi administration';
$string['pluginname'] = 'Jitsi';
$string['instruction'] = 'Click the button to access';
$string['access'] = 'Access';
$string['calendarstart'] = 'The videoconference \'{$a}\' start';
$string['allow'] = 'Start of videoconference';
$string['close'] = 'Finish of videoconference';
$string['nostart'] = 'The session has not started. You can access {$a} minutes before the start';
$string['finish'] = 'The session has finish.';
$string['server'] = 'Jitsi Server';
$string['serverexpl'] = 'Jitsi Server url';
$string['privacy:metadata:jitsi'] = 'In order to integrate with a Jitsi session, user data needs to be exchanged with that service.';
$string['privacy:metadata:jitsi:username'] = 'The username is sent from moodle to show to the other users of the Jitsi session';
$string['privacy:metadata:jitsi:avatar'] = 'The avatar is sent from moodle to show to the other users of the Jitsi session';
$string['noviewpermission'] = 'You do not have permission for view this Jitsi session';
$string['minpretime'] = 'Minutes to access';
$string['help'] = 'Help';
$string['helpex'] = 'Instruction text for show at Jitsi activity';
$string['identification'] = 'ID User';
$string['identificationex'] = 'ID to show in the session';
$string['username'] = 'Username';
$string['nameandsurname'] = 'Firstname + Lastname';
$string['tokennconfig'] = 'Token configuration';
$string['tokenconfigurationex'] = 'If you have a jitsi server with your own configuration (for example token configuration) you can use this by setting the following parameters. Empty for servers without token.';
$string['separator'] = 'Separator';
$string['separatorex'] = 'Define the field separator for the session name';
$string['sessionnamefields'] = 'Session name fields';
$string['sessionnamefieldsex'] = 'Fields that define the session name';
$string['securitybutton'] = 'Security Button';
$string['securitybuttonex'] = 'Show security button on session';
$string['invitebutton'] = 'Invite Options';
$string['invitebuttonex'] = 'Show invite options on session';
$string['appid'] = 'App_ID';
$string['appidex'] = 'App ID for token configuration';
$string['secret'] = 'Secret';
$string['secretex'] = 'Secret for token configuration';
$string['simultaneouscameras'] = 'Simultaneous cameras';
$string['simultaneouscamerasex'] = 'Number of simultaneous cameras';
$string['streamingbutton'] = 'Youtube Streaming';
$string['streamingbuttonex'] = 'Show streaming option.';
$string['blurbutton'] = 'Background options';
$string['blurbuttonex'] = 'Show background options';
$string['youtubebutton'] = 'Youtube sharing option';
$string['youtubebuttonex'] = 'Show youtube sharing option';
$string['watermarklink'] = 'Watermark Link';
$string['watermarklinkex'] = 'Watermark Link';
$string['finishandreturn'] = 'Finish and Return';
$string['finishandreturnex'] = 'Return to course when finish the session';
$string['password'] = 'Password';
$string['passwordex'] = 'Password for protect your sessions. Recommended if you use public server';
$string['alias'] = 'Alias';
$string['privatesession'] = '{$a} private session';
$string['myprivatesession'] = 'My private session';
$string['privatesessions'] = 'Private sessions';
$string['privatesessionsex'] = 'Add private sessions to user profiles';
$string['showavatars'] = 'Show avatars in Jitsi';
$string['showavatarsex'] = 'Show the avatar of the user in Jitsi. If the user has no profile picture this will load the default profile picture from Moodle instead of the initials Jitsi will show when no picture is set.';
$string['streamingconfig'] = 'Streaming configuration';
$string['streamingconfigex'] = 'Youtube Streaming. For integrate method you need add the following url to Authorized redirect URIs \'{$a}\' on Google api console.';
$string['oauthid'] = 'OAuth2 id';
$string['oauthidex'] = 'Oauth2 id google account';
$string['oauthsecret'] = 'OAuth2 secret';
$string['oauthsecretex'] = 'Oauth2 Secret google account';
$string['fullscreen'] = 'Full Screen';
$string['streamingandrecording'] = 'Streaming and recording';
$string['buttonopeninbrowser'] = 'Open in browser';
$string['buttonopenwithapp'] = 'Join this meeting using the app';
$string['buttondownloadapp'] = 'Download application';
$string['appaccessinfo'] = 'If you want to join the meeting using a mobile device, you will need the Jitsi Meet mobile application.';
$string['desktopaccessinfo'] = 'If you want to join the meeting, click on the button below to open Jitsi in your browser.';
$string['appinstalledtext'] = 'If you already have the app:';
$string['appnotinstalledtext'] = "If you don't have the app yet:";
$string['loginyoutube'] = 'Log In Youtube';
$string['logoutyoutube'] = 'Log Out \'{$a}\' account';
$string['deeplink'] = 'Deep Link';
$string['deeplinkex'] = 'Allows the transfer of the session to jitsi app.';
$string['accessto'] = 'Access to {$a}. Enter the name to be displayed.';
$string['accesstowithlogin'] = 'Access to {$a}.';
$string['accesstotitle'] = 'Access to {$a}';
$string['noinviteaccess'] = 'Guest access is currently not allowed.';
$string['startstream'] = 'Start stream';
$string['stopstream'] = 'Stop stream';
$string['URLguest'] = 'URL Guest ';
$string['copied'] = 'Copied to clipboard';
$string['streamingoption'] = 'Method';
$string['streamingoptionex'] = 'Choose if you want automatic integration with youtube streaming or use the streaming option of Jitsi interface';
$string['mailprivacy'] = 'Email only will use for gravatar option';
$string['record'] = 'Record option';
$string['recordex'] = 'Allow record option';
$string['entersession'] = 'Enter to session';
$string['guestform'] = 'Enter to guest form';
$string['records'] = 'Records';
$string['messageprovider:onprivatesession'] = 'User on private session';
$string['messageprovider:callprivatesession'] = 'Call to Jitsi private';
$string['userenter'] = '{$a} is on Jitsi';
$string['usercall'] = '{$a} calls you for a private Jitsi';
$string['hasentered'] = 'has entered on your private Jitsi session';
$string['iscalling'] = 'is calling you to enter on his private Jitsi';
$string['here'] = 'here';
$string['toenter'] = 'to enter';
$string['click'] = 'Click';
$string['privatesessiondisabled'] = 'Private sessions are disabled';
$string['validitytime'] = 'Validity time';
$string['validitytime_help'] = 'Link validity time';
$string['warningprivate'] = 'If you access, {$a} will be warned with a notification.';
$string['sharetoinvite'] = 'Share this link to invite someone to the session';
$string['invitations'] = 'Invitations';
$string['finishinvitation'] = 'Finish of invitation';
$string['linkexpiredon'] = 'This link expired on {$a}';
$string['invitationsnotactivated'] = 'The invitations is not activated';
$string['reactions'] = 'Reactions';
$string['reactionsex'] = 'Shows sound emoticons of applause, surprise, etc ...';
$string['updated'] = 'Updated';
$string['record'] = 'Record';
$string['editrecordname'] = 'Edit record name';
$string['newvaluefor'] = 'New value for ';
$string['acount'] = 'Account';
$string['acountex'] = 'Select the acount for streaming';
$string['acounts'] = 'Accounts';
$string['acountconnected'] = 'Account connected';
$string['deletesources'] = 'Delete record sources';
$string['(inuse)'] = ' (in use)';
$string['delete?'] = 'Delete and disconect?';
$string['login?'] = 'Log in?';
$string['tablelistjitsis'] = 'This table lists all videos to deleted stored in your streaming provider. Those that are not linked to any Jitsi are available to remove.';
$string['deletesource?'] = 'Delete? All jitsi records with this video record will be deleted';
$string['otheracount'] = 'Other acount';
