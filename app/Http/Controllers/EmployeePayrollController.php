<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Models\StaffSalary;
use App\Models\StaffSalaryPaid;
use Brian2694\Toastr\Facades\Toastr;

class EmployeePayrollController extends Controller
{
    // view page salary
    // public function salary()
    // {
    //     $users            = DB::table('users')->join('staff_salaries', 'users.user_id', '=', 'staff_salaries.user_id')->select('users.*', 'staff_salaries.*')->get(); 
    //     $userList         = DB::table('users')->get();
    //     $permission_lists = DB::table('permission_lists')->get();
    //     return view('payroll.employeesalary',compact('users','userList','permission_lists'));
    // }
    public function salary()
{
    $users = DB::table('users')
        ->join('staff_salaries', 'users.user_id', '=', 'staff_salaries.employee_id_auto')
        ->select('users.*', 'staff_salaries.*')
        ->get();

    $userList = DB::table('users')->select('user_id', 'name', 'phone_number')->get();
    // Select the 'user_id', 'name', and 'phone_number' fields from the 'users' table

    $permission_lists = DB::table('permission_lists')->get();

    return view('payroll.employeepersonaldashboard', compact('users', 'userList', 'permission_lists'));
}

public function eViewPaid()
{
    $users = DB::table('users')
        ->join('staff_salaries_paid', 'users.user_id', '=', 'staff_salaries_paid.employee_id_auto')
        ->select('users.*', 'staff_salaries_paid.*')
        ->get();

    $userList = DB::table('users')->select('user_id', 'name', 'phone_number')->get();
    // Select the 'user_id', 'name', and 'phone_number' fields from the 'users' table

    $permission_lists = DB::table('permission_lists')->get();

    return view('payroll.employeeviewsalarypaid', compact('users', 'userList', 'permission_lists'));
}


        // save record
public function saveRecord(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phone_number' => 'required|numeric', // Corrected 'number' to 'numeric'
        'number_of_kgs_harvested' => 'required|numeric|min:0',
        'shillings_per_kg' => 'required|numeric|min:0',
    ]);


    DB::beginTransaction();
    try {
        $salary = StaffSalary::updateOrCreate(['id' => $request->id]);
        $salary->name = $request->name;
        $salary->employee_id_auto = $request->employee_id_auto;
        $salary->phone_number = $request->phone_number;
        $salary->number_of_kgs_harvested = $request->number_of_kgs_harvested; // Updated field name
        $salary->shillings_per_kg = $request->shillings_per_kg; // Added field for shillings per kg
        $salary->estimated_payout = $request->number_of_kgs_harvested * $request->shillings_per_kg; // Calculated estimated payout
        $salary->save();

        DB::commit();
        Toastr::success('Created new Transaction successfully :)', 'Success');
        return redirect()->back();
    } catch (\Exception $e) {
        DB::rollback();
        dd($e->getMessage()); // Debugging: Display the error message
        Toastr::error('Add Transaction failed :(', 'Error');
        return redirect()->back();
    }

}


    // salary view detail
    public function salaryView($user_id)
    {
        $users = DB::table('users')
                ->join('staff_salaries', 'users.user_id', 'staff_salaries.user_id')
                ->join('profile_information', 'users.user_id', 'profile_information.user_id')
                ->select('users.*', 'staff_salaries.*','profile_information.*')
                ->where('staff_salaries.user_id',$user_id)->first();
        if(!empty($users)) {
            return view('payroll.salaryview',compact('users'));
        } else {
            Toastr::warning('Please update information user :)','Warning');
            return redirect()->route('profile_user');
        }
    }

    // update record
    public function updateRecord(Request $request)
    {
        $request->validate([
        'name' => 'required|string|max:255',
        'employee_mpesa_number' => 'required|numeric', // Corrected 'number' to 'numeric'
        'senders_mpesa_number' => 'required|numeric', // Corrected 'number' to 'numeric'
        'number_of_kgs_harvested' => 'required|numeric|min:0',
        'shillings_per_kg' => 'required|numeric|min:0',
        'amount_paid' => 'required|numeric|min:0',
    ]);


    DB::beginTransaction();
    try {
        $salary = StaffSalaryPaid::updateOrCreate(['id' => $request->id]);
        $salary->name = $request->name;
        $salary->employee_id_auto = $request->employee_id_auto;
        $salary->employee_mpesa_number = $request->employee_mpesa_number;
        $salary->senders_mpesa_number = $request->senders_mpesa_number;
        $salary->number_of_kgs_harvested = $request->number_of_kgs_harvested; 
        $salary->shillings_per_kg = $request->shillings_per_kg; // Added field for shillings per kg
        $salary->amount_paid = $request->number_of_kgs_harvested * $request->shillings_per_kg; // Calculated estimated payout
        $salary->save();

        DB::commit();
        Toastr::success('Transaction Paid successfully :)', 'Success');
        return redirect()->back();
    } catch (\Exception $e) {
        DB::rollback();
        dd($e->getMessage()); // Debugging: Display the error message
        Toastr::error('Transaction failed :(', 'Error');
        return redirect()->back();
    }
    }

    // delete record
    public function deleteRecord(Request $request)
    {
        DB::beginTransaction();
        try {

            StaffSalary::destroy($request->id);

            DB::commit();
            Toastr::success('Salary deleted successfully :)','Success');
            return redirect()->back();
            
        } catch(\Exception $e) {
            DB::rollback();
            Toastr::error('Salary deleted fail :)','Error');
            return redirect()->back();
        }
    }

    // payroll Items
    public function payrollItems()
    {
        return view('payroll.payrollitems');
    }
}
