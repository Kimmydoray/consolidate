<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consolidated;
use League\Csv\Reader;
use League\Csv\Statement;

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

    public function checkMatchData(Request $request) {
        return response()->json(['data' => $request]);
    }
}
