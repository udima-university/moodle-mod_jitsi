# Jitsi-Meet moodle plugin
This module allows creating **jitsi-meet videoconference** sessions fully integrated in Moodle. These videoconferences will use your Moodle username by displaying your username and avatar in videoconferences.

Jitsi-meet is an open-source videoconferencing solution that enables you to easily build and implement secure video conferencing solutions.

Whether you use the public server provided by Jitsi (meet.jit.si) or use your own Jitsi video conferencing server, with this plugin you can create video conferencing sessions in your Moodle courses in a simple way: just configure the domain of the Jitsi server and then, in the course, create a new Jitsi activity.

The module also allows the use of tokens to give moderation permissions to the roles you want using the *mod/jitsi:moderation* capability. (For this option it is necessary to have your own Jitsi server with token configuration).

Schedule your video conferences in time and make them accessible with The minutes as you want. In addition, the session will be shown in the Moodle calendar.

Jitsi allows video conference recording, direct transmission to YouTube, screen sharing, full screen display, statistics display, among other features and all these options are fully compatible with this module.

We have an Ansible ([ansible-jitsi-meet](https://github.com/udima-university/ansible-jitsi-meet)) to configure your own Jitsi server and for example to be able to use Token moderation.

More information about Jitsi's videoconferences: [jitsi.org](https://jitsi.org)

## Some module configuration options:
- **jitsi-domain**: Set the address of the jitsi server to be used here. By default you can use the public server meet.jit.si but you can use your own Jitsi server
- **jitsi-help**: Enter text here that you want to display to all users when they enter a jitsi resource.
- **jitsi-id**: Choose how you want to identify users in video conferences. Options are username or first and last name.
- **jitsi-sessionname**: You can configure how to name the videoconference rooms. You can use the course shortname, the jitsi resource ID and the session name. You can choose any combination of these three parameters.
- **jitsi-separator**: Choose a character with which to separate the jitsi-sesionname parameters.
- **jitsi-channellastcam**: With this parameter you can define the maximum number of cameras that users can see. If you set it to -1 there will be no limit but if for example you set it to 2, only the 2 cameras of the last two users that have been active in the videoconference will be displayed.
- **jitsi-showinfo**: Show or not the videoconference information
- **jitsi-blurbutton**: Show or not the option to blur the image background
- **jitsi-shareyoutube**: Show or not the option to share a YouTube video in the video conference.
- **jitsi-livebuttom**: Show or not the option to broadcast the video conference through YouTube.
