# Changelog
## v3.2.5 (2022041800)
### Changed
 * Some strings to strings file
### Fixed
 * Fixed 'core_completion\cm_completion_details' not found on moodle v<311 #93
 * Fixed session with long names records #94

## v3.2.4 (2022041800)
### Changed
 * Clean code api php Google. Lower size plugin
 * Corrections moodle style guideliness
### Fixed
 * Fixed destructure property on chrome

---

## v3.2.3 (2022041300)
### Fixed
 * Remove mdl prefix in sql userconnected

---

## v3.2.2 (2022041300)
### Added
 * Moodle 4.0 compatibility
### Fixed
 * Remove mdl prefix in getminutes function

---

## v3.2.0 ()
### Added
* Multi-account support for recordings.
* Notification when the user enters a private session.
* Allows guest users in a session. These guests can be users with a site account or without an account.
* Show the number of participants in a session and show assistance report.
* Add activity completion with number of minutes in a session.
* Better moderation without tokens.
* Allows to hide the Raise Hand Button.
* Jitsi reactions.
* Participants panel.
### Changed
* The recording button is replaced by a switch
* Default cameras now are 15.
* Watermark link now are deprecated.
* Enable as default the jitsi_invitebuttons, jitsifinishandreturn, jitsi_blurbutton, jitsi_reactions and jitsi_shareyoutube options in config.
* Only users with mod/jitsi:record should be able to launch native drop box recordings.
* Better placed introduction text and help text.
* Minutes to acces now apply only for users with moderation capability.
* Update Google Api Client to Version 2.12.1
### Fixed
* Background options 
* Fixed problem mod_jitsi_external::create_link implementation is missing
