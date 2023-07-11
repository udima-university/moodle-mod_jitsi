# Changelog
## v3.3.4 (2023062700)
### Added
* Round Robin: Add rotating shift queue for recordings. Recordings are now distributed among all session recordings if they are in the queue.
* Link to user profile on recording search page
* Mail to admin when doembeable get an error
### Fixed
* Fix problem with log error on doembedable function
* delete mod_jitsi_delete_recordsource service from services (#121)
### Changed
* Google api client to 2.15.0
* Search page for recordings now show thumbnails
* Remove heading for better appearence on moodle 4

## v3.3.3 (2023052400)
### Added
* Add page with search recordings for administrators

---

## v3.3.2 (2023050300)
### Added
* Add page with live recordings for administrators
* Recordings are locked by the user who started them. Preventing others from stopping them.
* IMPORTANT: new Scheduled tasks is enabled by default in order to delete recordings that are marked
  as deleted by teachers and new setting are included to set the retention period. Disable this task
  if you prefer to manually delete YouTube recordings.
* New section on settings for news and updates information.
* Max number of participants of a seession recorded is saved in source_record table.

### Fixed
* Fixed url for recordings to delete
* Fixed error with return create_stream function 

### Changed
* Pagination for the "Recordings available to delete" list.
* New view with tabs for recordings, help and participanting resume
* Recordings that don't have a link to the recording are not displayed on the jitsi page
* Google api 2.13.1 version
* Access buton now is primary button
* new getclientgoogleapi function to get the google api client
* Switch to record not visible for users without capability
* Help tab is always visible

---

## v3.3 (2022122300)
### Fixed
* Fix problem with timecreated when it's first time
* Solves problems with the French language.
### Changed
* Improvement in the counting of participants

---

## v3.3 (2022122300)
### Added
* New status field for better error handling in recordings
### Fixed
* Added timeclose and timeopen to coursemodule info
* Fixed validitytime check
### Changed
* New version Google api v.2.13.0

---

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

## v3.2.5 (2022041800)
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

---

## v3.1.2 (2021090100)
* Add validity time for link invitations

---

## v3.1.2 (2021072300)
* Fixed problem with Google API and https sites