<?php

namespace App\Console\Commands;

use App\Models\Place;
use Geocodio\Geocodio;
use Illuminate\Console\Command;
use Grimzy\LaravelMysqlSpatial\Types\Point;
use Illuminate\Database\QueryException;

class ImportDataFromGoogle extends Command
{
    const DEFAULT_STATE = 'FL';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import from Google Sheet';

    /**
     * @var null|Geocodio
     */
    protected $geoCoder = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command. Initializer
     *
     * @return int
     * @throws \Exception
     */
    public function handle()
    {
        $gSheetKey = env('GOOGLE_SHEETS_API_KEY');
        if (empty($gSheetKey)) {
            throw new \Exception('ERROR: GOOGLE_SHEETS_API_KEY constant is empty in .env file');
        }

        $geocoderKey = env('GEOCODIO_API_KEY');
        if (empty($geocoderKey)) {
            throw new \Exception('ERROR: GEOCODIO_API_KEY constant is empty in .env file');
        }

        $this->initGeoCoder($geocoderKey);

        echo "Requesting Google Sheet..." . PHP_EOL;
        $googleSheetData = $this->pullDataFromGoogleSheet(
            '1on0wFpfZ4dqV63bcfb6Xx1klFAV6P9LXqh-BI7G9luM',
            'Main Sheet',
            $gSheetKey
        );

        $this->processData($googleSheetData);

        return 0;
    }

    /**
     * Main processing method
     *
     * @param $googleSheetData
     */
    protected function processData($googleSheetData)
    {
        $header = false;
        foreach ($googleSheetData as $element) {
            if ($header === false) {
                $header = true;
                continue;
            }

            if (empty($element)) {
                continue;
            }

            echo 'Processing ' . trim($element[1]) . PHP_EOL;

            $compiledAddress = $this->getCompiledAddress($element);

            echo 'Requesting coordinates...' . PHP_EOL;

            $coordinates = $this->getCoordinates($compiledAddress);

            echo 'Saving...' . PHP_EOL;

            $this->saveData($element, $compiledAddress, $coordinates);

            echo PHP_EOL;
        }
    }

    /**
     * Creates/updates a record in DB (places)
     *
     * @param array $element
     * @param string $compiledAddress
     * @param array $coordinates
     */
    protected function saveData(array $element, string $compiledAddress, array $coordinates)
    {
        $place = Place::firstOrNew([
            'id' => trim($element[0]),
            'name' => trim($element[1]),
            'tel' => trim($element[8]),
            'email' => trim($element[10])
        ], [
            'address' => trim($compiledAddress),
            'category' => trim($element[4])
        ]);

        if (!empty($coordinates['lat'])) {
            $place->location = new Point($coordinates['lat'], $coordinates['lng']);
        }

        try {
            $place->save();
        } catch(QueryException $e) {
            $this->error($e);
        }
    }

    /**
     * Returns a string with compiled address
     *
     * @param array $element
     * @return string
     */
    protected function getCompiledAddress(array $element)
    {
        return implode(', ', [
            $element[5],// address
            self::DEFAULT_STATE,// state (default)
            $element[6],// city
            $element[7]// zip
        ]);
    }

    /**
     * Initialize Geocodio API
     *
     * @param string $geocoderKey
     */
    protected function initGeoCoder(string $geocoderKey)
    {
        $this->geoCoder = new Geocodio();
        $this->geoCoder->setApiKey($geocoderKey);
    }

    /**
     * Utilizes Geocodio to get lat/lng by given address
     *
     * @param string $address
     * @return array|string[]
     */
    protected function getCoordinates(string $address)
    {
        if (empty($address)) {
            return [
                'lat' => '',
                'lng' => ''
            ];
        }

        echo "- Requesting coordinates for address {$address}...";
        $coordinates = $this->geoCoder->geocode($address);

        if (is_object($coordinates) === false) {
            echo "FAILED" . PHP_EOL;
            return [
                'lat' => '',
                'lng' => ''
            ];
        }

        echo "OK" . PHP_EOL;
        return [
            'lat' => $coordinates->results[0]->location->lat,
            'lng' => $coordinates->results[0]->location->lng
        ];
    }

    /**
     * Pull data from Google Sheet
     *
     * @param $sheetId
     * @param $sheetName
     * @param $gKey
     * @return mixed
     */
    protected function pullDataFromGoogleSheet($sheetId, $sheetName, $gKey)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,
            "https://sheets.googleapis.com/v4/spreadsheets/{$sheetId}/values/".urlencode($sheetName)."?key={$gKey}"
        );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        $json = json_decode($output);
        return $json->values;
    }
}
