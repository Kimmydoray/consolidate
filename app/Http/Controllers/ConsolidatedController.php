<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consolidated;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

use Illuminate\Support\Facades\Log;


use Carbon\Carbon;

class ConsolidatedController extends Controller
{
    public function index(Request $request)
    {
        // Define the path to your CSV file
        $csvFilePath = public_path('csv/consolidate.csv');

        // Create a CSV reader instance
        $csv = Reader::createFromPath($csvFilePath, 'r');

        // Fetch the header record
        $headers = $csv->getHeader();
        
        // Set the header as the associative array keys
        $csv->setHeaderOffset(1);

        // Fetch the CSV data as an array
        $newColumnTotalMatch = 'Total Match';
        $newColumnAssetType = 'Asset Types';

        $data = $csv->getRecords();
        $header[] = $newColumnTotalMatch;
        
        // Create an array to hold the updated data
        $updatedData = [];
        
        // Loop through the CSV records one by one
        $count = 0;
        $total = 0;
        foreach ($data as $index => $record) {
            Log::info('Running Line ' . $index . "<br>");
            // if ($count <= 200) {
                //GET COMPARE DATES
                $origDate = Carbon::parse($record['In Service Date']);
                $startDate = $origDate->subDays(90)->toDateString();
                $endDate = $origDate->addDays(90)->toDateString();

                //GET COMPARE PERCENT

                $bookBasis = floatval(str_replace(',', '', $record[' Book Basis ']));
                $startCost = $bookBasis * 0.05;
                $startCost = $bookBasis - $startCost;
                $endCost = ($bookBasis * 0.05) + $bookBasis;

                // echo $startCost . "<br>" . $endCost;

                $data = Consolidated::whereBetween('u_acquisition_date', [$startDate, $endDate])
                    ->whereBetween('acquisition_cost', [$startCost, $endCost])->select('asset_type', 'acquisition_cost', 'name')->get();
                
                $total = $total + count($data);
                $record[$newColumnTotalMatch] = count($data);
                if (count($data)) {
                    $mergedAsset = '';
                    //UPDATE COLUMN DATA
                    if (count($data) > 1) {
                        $asset_types = [];
                        foreach($data as $key => $value) {
                            array_push($asset_types, $value['asset_type']);

                            if($data[$key]['acquisition_cost'] == $record[' Book Basis ']) {
                                $record['name'] = $data[0]['name'];
                                $record['In Scope'] = "Z";
                            } else {
                                $record['name'] = $record['Book'];
                                $record['In Scope'] = "ZZZZ";
                            }
                        }
                        $mergedAsset = implode(', ', $asset_types);
                    } else {
                        if($data[0]['acquisition_cost'] == $record[' Book Basis ']) {
                            $record['name'] = $data[0]['name'];
                            $record['In Scope'] = "Z";
                        } else {
                            $record['name'] = $record['Book'];
                            $record['In Scope'] = "ZZZZ";
                        }
                    }
                    
                    $record[$newColumnAssetType] = $mergedAsset;

                }
                $updatedData[] = $record;

            // } else {
            //     $csvUpdated = Writer::createFromPath($csvFilePath, 'w+');
            //     $csvUpdated->insertOne(array_merge($csv->getHeader(), [$newColumnTotalMatch, $newColumnAssetType]));
            //     $csvUpdated->insertAll($updatedData);

            //     return response()->json(['match' => $total]);
            // }
            // $count++;

        }
        $csvUpdated = Writer::createFromPath($csvFilePath, 'w+');
        $csvUpdated->insertOne(array_merge($csv->getHeader(), [$newColumnTotalMatch, $newColumnAssetType]));
        $csvUpdated->insertAll($updatedData);

        return response()->json(['match' => $total]);
       
    }


    // CHECK MATCH BETWEEN CSV AND DATABASE
    public function checkMatchData(Request $request) {
        if ($request->hasFile('csv_file')) {
            // Get the uploaded file from the request
            $uploadedFile = $request->file('csv_file');

            // Get the file path of the uploaded file
            $filePath = $uploadedFile->getRealPath();
            
            // Create a CSV reader instance
            $csv = Reader::createFromPath($filePath, 'r');

            // Fetch the header record
            $headers = $csv->getHeader();
            
            // Set the header as the associative array keys
            $csv->setHeaderOffset(1);

            // Fetch the CSV data as an array
            $newColumnTotalMatch = 'Total Match';
            $newColumnAssetType = 'Asset Types';

            $data = $csv->getRecords();
            $header[] = $newColumnTotalMatch;
            
            // Create an array to hold the updated data
            $updatedData = [];
            
            // Loop through the CSV records one by one
            $count = 0;
            $total = 0;
            foreach ($data as $index => $record) {
                Log::info('Running Line ' . $index . "<br>");
                // if ($count <= 200) {
                    //GET COMPARE DATES
                    $origDate = Carbon::parse($record['In Service Date']);
                    $startDate = $origDate->subDays(90)->toDateString();
                    $endDate = $origDate->addDays(90)->toDateString();

                    //GET COMPARE PERCENT

                    $bookBasis = floatval(str_replace(',', '', $record[' Book Basis ']));
                    $startCost = $bookBasis * 0.05;
                    $startCost = $bookBasis - $startCost;
                    $endCost = ($bookBasis * 0.05) + $bookBasis;

                    // echo $startCost . "<br>" . $endCost;

                    $data = Consolidated::whereBetween('u_acquisition_date', [$startDate, $endDate])
                        ->whereBetween('acquisition_cost', [$startCost, $endCost])->select('asset_type', 'acquisition_cost', 'name')->get();
                    
                    $total = $total + count($data);
                    $record[$newColumnTotalMatch] = count($data);
                    if (count($data)) {
                        $mergedAsset = '';
                        //UPDATE COLUMN DATA
                        if (count($data) > 1) {
                            $asset_types = [];
                            foreach($data as $key => $value) {
                                array_push($asset_types, $value['asset_type']);

                                if($data[$key]['acquisition_cost'] == $record[' Book Basis ']) {
                                    $record['name'] = $data[0]['name'];
                                    $record['In Scope'] = "Z";
                                } else {
                                    $record['name'] = $record['Book'];
                                    $record['In Scope'] = "ZZZZ";
                                }
                            }
                            $mergedAsset = implode(', ', $asset_types);
                        } else {
                            if($data[0]['acquisition_cost'] == $record[' Book Basis ']) {
                                $record['name'] = $data[0]['name'];
                                $record['In Scope'] = "Z";
                            } else {
                                $record['name'] = $record['Book'];
                                $record['In Scope'] = "ZZZZ";
                            }
                        }
                        
                        $record[$newColumnAssetType] = $mergedAsset;

                    }
                    $updatedData[] = $record;

                // } else {
                //     $csvUpdated = Writer::createFromPath($csvFilePath, 'w+');
                //     $csvUpdated->insertOne(array_merge($csv->getHeader(), [$newColumnTotalMatch, $newColumnAssetType]));
                //     $csvUpdated->insertAll($updatedData);

                //     return response()->json(['match' => $total]);
                // }
                // $count++;

            }
            $csvUpdated = Writer::createFromPath($csvFilePath, 'w+');
            $csvUpdated->insertOne(array_merge($csv->getHeader(), [$newColumnTotalMatch, $newColumnAssetType]));
            $csvUpdated->insertAll($updatedData);

            return response()->json(['match' => $total]);
        }
    }
}
