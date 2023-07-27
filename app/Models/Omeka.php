<?php

namespace App\Models;

use Illuminate\Support\Facades\Http;
use NumberFormatter;

class Omeka
{
    public $itemSets;

    /**
     * @return mixed
     */
    public function getItemSets()
    {
        return $this->itemSets;
    }

    public function createItem($record)
    {
        // Check if item exists
        $items = $this->search($record->mFolderNumber);
        // TODO: This doesn't work going from my virtual machine to Laragon. I'll probably need to set up Omeka on Forge.
        if (count($items) > 0) {
            $id = $items[0]['o:id'];

            $response = Http::withBody($this->getItemTemplate($record), 'application/json')
                ->put(config('omeka.url') . '/api/items/' . $id . '?key_identity=' . config('omeka.key') . '&key_credential=' . config('omeka.secret'));
        } else {
            $response = Http::withBody($this->getItemTemplate($record), 'application/json')
                ->post(config('omeka.url') . '/api/items?key_identity=' . config('omeka.key') . '&key_credential=' . config('omeka.secret'));
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function search($identifier)
    {
        $response = Http::get(config('omeka.url') . '/api/items?fulltext_search=&property[0][joiner]=and&property[0][property]=10&property[0][type]=eq&resource_class_id[]=&item_set_id[]=&submit=Search&property[0][text]=' . $identifier);

        return json_decode($response->body(), true);
    }

    public static function getCenturyFromYearRange($date)
    {
        $year = intval(substr($date, 0, 4));
        $century = (int) ($year / 100) + 1;
        $nf = new NumberFormatter('en_US', NumberFormatter::ORDINAL);
        return $nf->format($century);
    }


    public function getItemTemplate(Record $record)
    {
        $payload = [
            "@type" => "o:Item",
            "o:is_public" => true,
            /*"o:owner" => [
                "@id" => config('omeka.url') . "/api/users/" . config('omeka.owner'),
                "o:id" => config('omeka.owner')
            ],*/
            "o:resource_class" => null,
            "o:resource_template" => null,
            "o:title" => $record->mCollection . " " . $record->mCodexNumberNew,
            "o:item_set" => [],
            "o:site" => [
                [
                    "@id" => config('omeka.url') . "/api/sites/" . config('omeka.site'),
                    "o:id" => config('omeka.site')
                ]
            ],
            "dcterms:title" => [
                [
                    "type" => "literal",
                    "property_id" => 1,
                    "property_label" => "Title",
                    "is_public" => true,
                    "@value" => $record->mCollection . " " . $record->mCodexNumberNew
                ]
            ],
            "dcterms:alternative" => [
                [
                    "type" => "literal",
                    "property_id" => 17,
                    "property_label" => "Alternative Title",
                    "is_public" => true,
                    "@value" => $record->mCodexNumberNew
                ]
            ],
            "dcterms:hasPart" => [
                [
                    "type" => "literal",
                    "property_id" => 34,
                    "property_label" => "Has Part",
                    "is_public" => true,
                    "@value" => $record->part
                ]
            ],
            "dcterms:date" => [
                [
                    "type" => "literal",
                    "property_id" => 7,
                    "property_label" => "Date",
                    "is_public" => true,
                    "@value" => $record->mCentury
                ]
            ],
            "dcterms:temporal" => [
                [
                    "type" => "literal",
                    "property_id" => 41,
                    "property_label" => "Temporal Coverage",
                    "is_public" => true,
                    "@value" => self::getCenturyFromYearRange($record->mCentury)
                ]
            ],
            "dcterms:coverage" => [
                [
                    "type" => "literal",
                    "property_id" => 14,
                    "property_label" => "Coverage",
                    "is_public" => true,
                    "@value" => $record->mCountry
                ]
            ],
            "dcterms:language" => [
                [
                    "type" => "literal",
                    "property_id" => 12,
                    "property_label" => "Language",
                    "is_public" => true,
                    "@value" => $record->mLanguage
                ]
            ],
            "dcterms:bibliographicCitation" => [
                [
                    "type" => "literal",
                    "property_id" => 48,
                    "property_label" => "Bibliographic Citation",
                    "is_public" => true,
                    "@value" => $record->mTextReference,
                ]
            ],
            "dcterms:hasFormat" => [
                [
                    "type" => "literal",
                    "property_id" => 38,
                    "property_label" => "Has Format",
                    "is_public" => true,
                    "@value" => "https://data.metascripta.org/iiif/VFL_" . $record->mFolderNumber . ".json"
                ]
            ],
            "dcterms:hasVersion" => [
                [
                    "type" => "literal",
                    "property_id" => 28,
                    "property_label" => "Has Version",
                    "is_public" => true,
                    "@value" => "https://metascripta-01.s3.amazonaws.com/" . $record->mFolderNumber . ".pdf"
                ]
            ],
            "dcterms:identifier" => [
                [
                    "type" => "literal",
                    "property_id" => 10,
                    "property_label" => "Identifier",
                    "is_public" => true,
                    "@value" => $record->mFolderNumber
                ]
            ],
            "dcterms:source" => [
                [
                    "type" => "literal",
                    "property_id" => 11,
                    "property_label" => "Source",
                    "is_public" => true,
                    "@value" => $record->roll
                ]
            ],
            "dcterms:created" => [
                [
                    "type" => "literal",
                    "property_id" => 20,
                    "property_label" => "Date Created",
                    "is_public" => true,
                    "@value" => $record->mDateDigitized
                ]
            ],
            "dcterms:publisher" => [
                [
                    "type" => "literal",
                    "property_id" => 5,
                    "property_label" => "Publisher",
                    "is_public" => true,
                    "@value" => "Saint Louis University Libraries"
                ]
            ],
            "dcterms:rights" => [
                [
                    "type" => "literal",
                    "property_id" => 15,
                    "property_label" => "Rights",
                    "is_public" => true,
                    "@value" => "CC0 1.0 Universal"
                ]
            ],
            "dcterms:isPartOf" => [
                [
                    "type" => "literal",
                    "property_id" => 33,
                    "property_label" => "Is Part Of",
                    "is_public" => true,
                    "@value" => $record->mCollection
                ]
            ]
        ];

        return json_encode($payload);
    }
}