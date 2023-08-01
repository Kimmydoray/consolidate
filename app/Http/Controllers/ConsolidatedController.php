<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consolidated;
use Carbon\Carbon;

class ConsolidatedController extends Controller
{
    public function index(Request $request)
    {
        //GET COMPARE DATES
        $origDate = Carbon::parse("5/31/07");
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

        return response()->json(['data' => $data]);
    }
}
