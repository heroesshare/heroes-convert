# Heroes Convert (Heroes of the Storm)

File format conversions from HeroesDataParser to heroes-talents

## Installation

Clone or download the latest source from the repo:
[heroes-convert](https://github.com/tattersoftware/heroes-convert)

## Usage

**heroes-convert** takes [Heroes of the Storm](https://heroesofthestorm.com) game data
parsed and extracted by [HeroesDataParser](https://github.com/HeroesToolChest/HeroesDataParser)
and simplifies it and reformats it for [heroes-talents](https://github.com/heroespatchnotes/heroes-talents).

### Gamedata

In order to use **heroes-convert** you must already have the extracted game data. The easiest
way to acquire the data is from [heroes-data](https://github.com/HeroesToolChest/heroes-data),
a repo of pre-parsed game data from HeroesDataParser.

In the **src** folder there is an executable script, `heroes-convert`. This script takes
as parameters the localized herodata file and a locale-specific gamestrings file:

	./heroes-convert herodata_localized.json gamestrings_enus.json

You may supply an optional third parameter for an output directory, or it will default to
the current directory in the **hero** subfolder.

### Images

In addition to the JSON data files, talent icons can be converted to their **heroes-talents**
equivalent with the `images-convert` command. You must already have the extracted image data,
for example from [heroes-images](https://github.com/HeroesToolChest/heroes-images).
Additionally you need [ImageMagick](https://imagemagick.org) installed to do the conversion,
and a bulk `rename` command to remove apostrophes (included with most Linux distros; macOS
[see here](https://devhints.io/rename)).

The executable script takes the **abilitytalents** directory as a parameter:

	./images-convert /path/to/images/abilitytalents

You may supply an optional second parameter for an output directory, or it will default to
the current directory in the **talents** subfolder.
