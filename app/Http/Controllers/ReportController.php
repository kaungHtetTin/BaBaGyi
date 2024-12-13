<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LotteryType;
use App\Models\Number;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Models\Clock;

class ReportController extends Controller
{
    public function index(Request $req){
        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;
        $lottery_type = LotteryType::find($lottery_type_id);
        $clock = Clock::find($clock_id);

        $numbers = Number::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('report','>',0)
        ->get();

        $reports = Report::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->orderBy('id','desc')
        ->paginate(100);

        return view('admin.reports',[
            'page_name'=>'Reports',
            'lottery_type'=>$lottery_type,
            'clock'=>$clock,
            'numbers' => $numbers,
            'reports'=>$reports,
        ]);
    }

    public function store(Request $req){
        $req->validate([
            'lottery_type_id'=>'required',
            'clock_id'=>'required',
        ]);

        $lottery_type_id = $req->lottery_type_id;
        $clock_id = $req->clock_id;

        $numbers = Number::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('report','>',0)
        ->get();

        if(count($numbers)<=0){
            return back()->with('error','Error Reporting');
        }

        $report = new Report();
        $report->lottery_type_id = $lottery_type_id;
        $report->clock_id = $clock_id;
        $report->save();

        $report_details = [];
        foreach($numbers as $number){
            $report_details [] = [
                'report_id'=>$report->id,
                'number_id'=>$number->id,
                'amount'=>$number->report,
            ];
        }

        ReportDetail::insert($report_details);
        $numbers = Number::where('lottery_type_id',$lottery_type_id)
        ->where('clock_id',$clock_id)
        ->where('report','>',0)
        ->update(['report'=>0]);

        return view('admin.reports-detail',[
            'page_name'=>'Report',
            'report'=>$report,
        ]);

    }

    public function show($id){
        $report = Report::find($id);
        return view('admin.reports-detail',[
            'page_name'=>'Report',
            'report'=>$report,
        ]);
    }
}
