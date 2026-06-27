=== Esefoke Audio ===

Contributors: esefoke
Tags: audio, text-to-speech, edge-tts, narration, podcast, accessibility
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.4.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Convert WordPress posts into natural audio narration using Microsoft Edge-TTS.

== Description ==

Esefoke Audio allows WordPress publishers to convert articles into high-quality audio narration using Microsoft Edge-TTS voices.

Readers can listen to articles directly from your website, improving accessibility, engagement, and content consumption.

The plugin supports manual generation, automatic generation, regeneration, and bulk audio creation.

== Features ==

* Microsoft Edge-TTS integration
* Natural neural voices
* Multiple voice options
* Speech speed control
* Manual audio generation
* Automatic audio generation on publish
* Bulk generation of missing audio
* Regenerate existing audio
* Audio library manager
* Download audio files
* Delete audio files
* Dashboard statistics
* Configurable Edge-TTS path
* Frontend audio player
* Multiple WordPress installation support

== Requirements ==

* WordPress 6.0 or higher
* PHP 7.4 or higher
* Python installed
* Edge-TTS installed

Install Edge-TTS:

pip install edge-tts

== Installation ==

1. Upload the plugin ZIP file.
2. Activate the plugin.
3. Install Python.
4. Install Edge-TTS.
5. Open the plugin settings page.
6. Enter the full path to edge-tts.exe.
7. Choose your preferred voice.
8. Save settings.
9. Generate audio from the Generate Audio page.

== Usage ==

=== Generate Single Audio ===

Select a post and click "Generate Audio."

=== Generate Missing Audio ===

Generate audio for all published posts that do not yet have narration.

=== Auto Generate ===

Enable Auto Generate to automatically create audio whenever a new post is published.

=== Regenerate Audio ===

Replace an existing audio file with newly generated narration.

== Available Voices ==

* Jenny (US Female)
* Guy (US Male)
* Sonia (UK Female)
* Ryan (UK Male)

== Frequently Asked Questions ==

= Does this require an API key? =

No. Edge-TTS is free and does not require an API key.

= Can I change the voice? =

Yes. Multiple voices are supported.

= Can I change the speaking speed? =

Yes. Speech speed can be adjusted in the settings.

= Can I regenerate audio? =

Yes. Existing audio files can be regenerated.

= Does it work automatically? =

Yes. Auto Generate can create audio when posts are published.

== Changelog ==

= 1.4.0 =

* Added configurable Edge-TTS path
* Added automatic audio generation
* Added bulk audio generation
* Added regeneration support
* Added dashboard statistics
* Added speech speed control
* Added multiple voices
* Added audio library

= 1.3.0 =

* Added Edge-TTS integration
* Added audio generation
* Added audio storage
* Added frontend player

== Upgrade Notice ==

= 1.4.0 =

This release introduces a configurable Edge-TTS path, automatic generation, and bulk audio generation features.

== Credits ==

Developed by Frank Okenegede.

Website: https://esefoke.xyz
