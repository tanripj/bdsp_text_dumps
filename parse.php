<?php
declare(strict_types=1);

use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use League\Csv\Writer;

require __DIR__ . '/vendor/autoload.php';

// The names of the languages as used in the bdsp text dump files.
$languages = [
	'jpn',
	'english',
	'french',
	'german',
	'italian',
	'spanish',
	'korean',
	'jpn_kanji',
	'simp_chinese',
	'trad_chinese',
];

$filesToParse = [
	// The data in this first file isn't really necessary, but it's a good file to use to test the overall script.
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_monsname.json', 'outputFilename' => 'output/pokemon_names.csv'],

	// Pokédex entries.
	['inputFilename' => 'input/1.3.0/[language]/[language]_dp_pokedex_diamond.json', 'outputFilename' => 'output/pokedex_entries_diamond.csv'],
	['inputFilename' => 'input/1.3.0/[language]/[language]_dp_pokedex_pearl.json', 'outputFilename' => 'output/pokedex_entries_pearl.csv'],

	// Ability names and descriptions.
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_tokusei.json', 'outputFilename' => 'output/ability_names.csv'],
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_tokuseiinfo.json', 'outputFilename' => 'output/ability_descriptions.csv'],

	// Item names and descriptions.
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_itemname.json', 'outputFilename' => 'output/item_names.csv'],
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_iteminfo.json', 'outputFilename' => 'output/item_descriptions.csv'],

	// Move names and descriptions.
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_wazaname.json', 'outputFilename' => 'output/move_names.csv'],
	['inputFilename' => 'input/1.3.0/[language]/[language]_ss_wazainfo.json', 'outputFilename' => 'output/move_descriptions.csv'],
];

foreach ($filesToParse as $file) {
	$outputRecords = [];

	foreach ($languages as $language) {
		$inputFilename = str_replace('[language]', $language, $file['inputFilename']);

		$items = Items::fromFile($inputFilename, [
			'pointer' => '/labelDataArray',
			'decoder' => new ExtJsonDecoder(true),
		]);

		foreach ($items as $key => $value) {
			$str = [];
			foreach ($value['wordDataArray'] ?? [] as $wordData) {
				$str[] = $wordData['str'];
			}
			$str = implode("\\n", $str);

			$outputRecords[$key][$language] = $str;
		}
	}

	$outputCsv = Writer::createFromPath($file['outputFilename'], 'w+');
	$outputCsv->insertOne($languages);
	foreach ($outputRecords as $key => $outputRecord) {
		$outputCsv->insertOne($outputRecord);
	}
}
