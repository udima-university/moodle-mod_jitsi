# Changelog
## v3.3.2 (2023050300)
### Added
* Add page with live recordings for administrators
* Recordings are locked by the user who started them. Preventing others from stopping them.
* IMPORTANT: new Scheduled tasks is enabled by default in order to delete recordings that are marked
  as deleted by teachers and new setting are included to set the retention period. Disable this task
  if you prefer to manually delete YouTube recordings.
* New section on settings for news and updates information.

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