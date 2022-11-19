# Changelog
## v3.2.18 (2022111900)
### Added
 * Whiteboard added
### Fixed
 * Fixed double competibility output on view.php for moodles v>311
 * Fixes issue where teachers couldn't assign capabilities
 * #110 Plugin v.3.2.17 does not work when jitsi_password is configurated
### Changed
 * Disables all invite functions from the app
 * Embedable value based on youtube response
---

## v3.2.17 (2022110701)
### Added
 * Add gues link information in mod_form and view page
 * New capabilities deleterecord and editrecordname
### Fixed
 * Fixed exception - Call to undefined method admin_settingpage::hide_if() on versions less than 37
 * Fixed problems with special characteres for chrome 
---

## v3.2.16 (2022101700)
### Added
 * Shows the author of a recording in the deleted list
 * Add to log when user press button record, cam, microphone, share desktop and end button
 * Send mails to admins when record fails
 * Register participating when user logged enter with guest link
### Fixed 
 * Removes warning when restoring with user data and no recordings
 * When making a local recording, the session is being recorded banner is not displayed
 * Remove unrecorded videos from jitsi (scheduled videos coming soon)
 * Fixes issue where students were triggering the switch to record at 5 seconds
 * Fixed Dom Focused problem when copy to clipboard on chrome
 * Enable recording service with latest versions of jitsi 
### Changed
 * Better handling of api requests
 * Disable Grant Moderator button
 * Disabled record button when teacher enter with guest link
 ---
