<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consolidated;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\Writer;

use Carbon\Carbon;

class ConsolidatedController extends Controller
{
    public function index(Request $request)
    {
        // Define the path to your CSV file
        $csvFilePath = public_path('csv/consolidate.csv');

        // Create a CSV reader instance
        $csv = Reader::createFromPath($csvFilePath, 'r');
        
        // $newColumnName1 = 'Total Match';
        // $header = $csv->getHeader();
        // $header[] = $newColumnName1;
        // $csvUpdated->insertOne($header);

        
        // $csv->insertBeforeHeader($newColumnName1);

        // $newColumnName2 = 'Asset Types';
        // $csv->insertBeforeHeader($newColumnName2);

        // Fetch the header record
        $headers = $csv->getHeader();
        
        // Set the header as the associative array keys
        $csv->setHeaderOffset(1);
        
        // Loop through the CSV records one by one
        $count = 0;
        $total = 0;
        foreach ($csv->getRecords() as $record) {
            if ($count <= 7) {
                //GET COMPARE DATES
                $origDate = Carbon::parse($record['In Service Date']);
                $startDate = $origDate->subDays(90)->toDateString();
                $endDate = $origDate->addDays(90)->toDateString();

                //GET COMPARE PERCENT

                $bookBasis = str_replace(',', '', "395.00");
                $startCost = ($bookBasis * 0.05);
                $startCost = $bookBasis - $startCost;
                $endCost = ($bookBasis * 0.05) + $bookBasis;

                // echo $startCost . "<br>" . $endCost;

                $data = Consolidated::whereBetween('u_acquisition_date', [$startDate, $endDate])
                    ->whereBetween('acquisition_cost', [$startCost, $endCost])->get();
                $total = $total + count($data);
                $count++;
            } else {
                return response()->json(['match' => $total]);
            }

        }
       
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
            
            $newColumnName1 = 'Total Match';
            $csv->insertBeforeHeader($newColumnName1);

            $newColumnName2 = 'Asset Types';
            $csv->insertBeforeHeader($newColumnName2);

            // Fetch the header record
            $headers = $csv->getHeader();
            
            // Set the header as the associative array keys
            $csv->setHeaderOffset(1);
            
            // Loop through the CSV records one by one
            $count = 0;
            $total = 0;
            foreach ($csv->getRecords() as $record) {
                if ($count <= 7) {
                    //GET COMPARE DATES
                    $origDate = Carbon::parse($record['In Service Date']);
                    $startDate = $origDate->subDays(90)->toDateString();
                    $endDate = $origDate->addDays(90)->toDateString();

                    //GET COMPARE PERCENT

                    $bookBasis = str_replace(',', '', "395.00");
                    $startCost = ($bookBasis * 0.05);
                    $startCost = $bookBasis - $startCost;
                    $endCost = ($bookBasis * 0.05) + $bookBasis;

                    // echo $startCost . "<br>" . $endCost;

                    $data = Consolidated::whereBetween('u_acquisition_date', [$startDate, $endDate])
                        ->whereBetween('acquisition_cost', [$startCost, $endCost])->get();
                    $total = $total + count($data);
                    if(count($data)) {
                        
                    }
                    $count++;
                } else {
                    return response()->json(['match' => $total]);
                }
            }
        }

        return response()->json(['data' => $request]);
    }
}
