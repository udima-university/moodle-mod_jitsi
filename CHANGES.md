# Changelog
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

## v3.2.11 (2022070601)
### Added
 * When a teacher marks a video as deleted it should be hidden on youtube. #105
### Fixed 
 * The message that the session has not started appears wrong when accessing through invite #102
 * Jitsis with a lot of recordings takes a long time to load the access page #104 
 * get_objectid_mapping function missing when importing logs #106
 ### Changed
 * Data type mismatch in name field of jitsi_record table RDM #107
 ---

## v3.2.8 (2022061600)
### Added
 * New version api google (v2.12.6)
### Fixed 
 * Ilegal character with substr function #100
 * Missing language string #81

---

## v3.2.7 (2022060100)
### Added
 * New version api google (v2.12.4)
 * Validate link invitation with startdate #98
 * Added compatibility with 8x8 servers
### Changed
 * jitsi_channellastcam deprecated

---

## v3.2.6 (2022051300)
### Added
 * Check that the finish date is always later than the starting date #96
### Changed
 * Default time for invitations are time+24h. Error validitytime now are alert tipe
### Fixed
 * For versions <311 add jitsi_get_completion_state to deprecatedlib. Before these versions gave an error when using custom completions
 * Fixed capability error when teacher edit record name
 
---

## v3.2.5 (2022042200)
### Changed
 * Some strings to strings file
### Fixed
 * Fixed 'core_completion\cm_completion_details' not found on moodle v<311 #93
 * Fixed session with long names records #94

---

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
