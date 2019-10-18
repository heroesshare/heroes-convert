# heroes-convert

Convert HeroesDataParser to heroes-talents, for Heroes of the Storm

## Installation

Clone or download the latest source from the repo:
[heroes-convert](https://github.com/tattersoftware/heroes-convert)

## Description

**heroes-convert** takes [Heroes of the Storm](https://heroesofthestorm.com) game data
parsed and extracted by [HeroesDataParser](https://github.com/HeroesToolChest/HeroesDataParser)
and simplifies it and reformats it for [heroes-talents](https://github.com/heroespatchnotes/heroes-talents).

## Setup

In order to use **heroes-convert** you must already have the extracted game data. The easiest
way to acquire the data is from [heroes-data](https://github.com/HeroesToolChest/heroes-data),
a repo of pre-parsed game data from HeroesDataParser.

## Usage

In the **src** folder there is an executable script, `heroes-convert`. This script takes
as parameters the localized herodata file and a locale-specific gamestrings file:

	./heroes-convert herodata_localized.json gamestrings_enus.json

You may supply an optional third parameter for an output directory, or it will default to
the current directory in the **hero** subfolder.
