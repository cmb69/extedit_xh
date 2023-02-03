# Extedit_XH

Extedit_XH facilitates to have an arbitrary amount of so called “extedits”,
i.e. content areas, which can be edited by users logged in via
[Memberpages_XH](https://github.com/cmsimple-xh/memberpages)
or [Register_XH](https://github.com/cmb69/register_xh).
That allows a very limited multi-user capability without the need
to grant these users full administration authorisation.
The plugin offers basically the same functionality as the
One Page for simpleMultiUser plugin,
but uses the editor of CMSimple_XH.
For security reasons the file browser is replaced with a minimal image picker.

- [Requirements](#requirements)
- [Download](#download)
- [Installation](#installation)
- [Settings](#settings)
- [Usage](#usage)
  - [Image Picker](#image-picker)
- [Troubleshooting](#troubleshooting)
- [License](#license)
- [Credits](#credits)

## Requirements

Extedit_XH is a plugin for CMSimple_XH.
It requires CMSimple_XH ≥ 1.7.0 and
PHP ≥ 7.1.0 with the fileinfo and session extensions.

## Download

The [lastest release](https://github.com/cmb69/extedit_xh/releases/latest)
is available for download on Github.

## Installation

The installation is done as with many other CMSimple\_XH plugins. See the
[CMSimple_XH wiki](https://wiki.cmsimple-xh.org/?for-users/working-with-the-cms/plugins#id3_install-plugin)
for further details.

1. **Backup the data on your server.**
1. Unzip the distribution on your computer.
1. Upload the whole directory `extedit/` to your server into the `plugins/`
   directory of CMSimple_XH.
1. Set write permissions for the subdirectories `config/` and `languages/`.
1. Navigate to `Plugins` → `Extedit` in the back-end
   to check if all requirements are fulfilled.

## Settings

The configuration of the plugin is done as with many other CMSimple_XH plugins in
the back-end of the Website. Select `Plugins` → `Extedit`.

You can change the default settings of Extedit_XH under `Config`. Hints for
the options will be displayed when hovering over the help icon with your
mouse.

Localization is done under `Language`. You can translate the character
strings to your own language if there is no appropriate language file available,
or customize them according to your needs.</p>

<!-- TODO: customization of the editor -->

## Usage

To embed an “extedit” on a page, insert the following:

    {{{extedit('%USERNAME%', '%TEXTNAME%')}}}

To embed an “extedit” in the template, insert the following:

    <?=extedit('%USERNAME%', '%TEXTNAME%')?>

Note: if “extedits” are embedded in the template or a newsbox, the configuration
option `Allow` → `Template` has to be enabled.

The parameters have the following meaning:

- `%USERNAME%`:
  The name of the Register_XH or Memberpages_XH user, who may edit the content.
  If `*` is given as username, *all* authenticated users may edit the content.
  Alternatively, you can specify multiple usernames
  separated by comma (without any whitespace) here.

- `%TEXTNAME%`:
  The unique name of the “extedit”. Omit this parameter to use the heading
  of the containing page. This parameter is mandatory, if you place the
  `extedit()` call in the template or a newsbox.

When authorized users are logged in, they are presented an `Edit` link,
where they can edit the contents of the “extedit”.
They can insert images via the editor, but have no access to the file browser –
only a simple [Image Picker](#image-picker) is available.
In preview mode the admin of the Website is also able to edit the “extedit”,
and has access to the file browser as usual.
Visitors will only see the content of the “extedit”.

It is possible to have an arbitrary amount of “extedits” on a single page, all
of them assigned to a certain user, each of them assigned to a different user,
or a mixture thereof. To avoid that a user inadvertently overwrites the changes
made by another user, a simple optimistic concurrency lock is implemented.

The contents of all “extedits” are stored in the automatically created subfolder
`extedit/` of the current `content/` folder, each in a single file.
The filename is made from the `%TEXTNAME%` by stripping all invalid characters
(only alphanumeric characters and hyphens are allowed).
*Therefore all respectively stripped `%TEXTNAME%`s have
to be unique for each language of the CMSimple_XH installation.*

Caveat: If you omit the `%TEXTNAME%` parameter, the heading of the page will
be used instead. If you later change the page heading, the “extedit” file has to
be manually renamed. Furthermore it is perfectly valid to have the same page
heading more than once in CMSimple_XH in different subtrees of the TOC, but that
does not work for Extedit_XH. So it is probably better to always explicitly
specify the `%TEXTNAME%` parameter.

It is possible to use plugin calls in the “extedits” (what has to be enabled
in the configuration), but this is of limited use, as the users will not be able
to actually administrate the plugins. However, some plugins do not need such
management, so they can be used ad hoc, and others could be prepared
by the admin.

### Image Picker

As the filebrowser is only available for the administrator of the Website
for security reasons, Extedit_XH offers a very simplistic image picker for users
who are logged in via Memberpages_XH or Register_XH.

By default the user has only access to his own subfolder of the image folder
(which is normally `userfiles/images/`). This subfolder has to have the same name
as the user and must be created by the administrator. The user can
upload images to this folder, but cannot delete or rename these images.
Furthermore, the user is not able to access subfolders of his own
image folder.

## Limitations

- The image picker is currently available only for TinyMCE4 and CKEditor.
- Advanced management of the “extedit” files (e.g. deleting and renaming) has
  to be done via FTP.

## Troubleshooting

Report bugs and ask for support either on [Github](https://github.com/cmb69/extedit_xh/issues)
or in the [CMSimple_XH Forum](https://cmsimpleforum.com/).

## License

Extedit_XH is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Extedit_XH is distributed in the hope that it will be useful,
but *without any warranty*; without even the implied warranty of
*merchantibility* or *fitness for a particular purpose*. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Extedit_XH.  If not, see <https://www.gnu.org/licenses/>.

© 2013-2023 Christoph M. Becker

Danish translation © 2013 Jens Maegaard

## Credits

The plugin logo is designed by [Alessandro Rei](http://www.mentalrey.it/).
Many thanks for publishing this icon under GPL.

This plugin uses free applications icons from [Aha-Soft](http://www.aha-soft.com/).
Many thanks for making these icons freely available.

Many thanks to the community at the [CMSimple_XH Forum](https://www.cmsimpleforum.com)
for tips, suggestions and testing. Especially, I like to thank *Ulrich*, *svasti* and
*Hartmut* for their early feedback. Also I like to thank *Ele* for reporting a
critical bug early in the RC stage, and helping to resolve it.

And last but not least many thanks to [Peter Harteg](https://www.harteg.dk/),
the “father” of CMSimple, and all developers of [CMSimple_XH](https://www.cmsimple-xh.org/)
without whom this amazing CMS would not exist.
