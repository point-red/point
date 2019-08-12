<style>
	th {
		font-family: 'Helvetica';
		font-weight: normal;
		font-style: normal;
		font-variant: normal;
		font-size: 12;
	}
	td {
		font-family: 'Helvetica';
		font-weight: normal;
		font-style: normal;
		font-variant: normal;
		font-size: 9;
	
	}
</style>
<table width="100%" style="border: 1px solid #000;" cellspacing="4">
    <thead>
    	<tr>
	        <th colspan="8" style="text-align:center;"><b>INVOICE</b></th>
	    </tr>
    	<tr>
	        <th width="5%">
	        	<br/>
	        </th>
	        <th width="35%"></th>
	        <th width="12%"></th>
	        <th width="12%"></th>
	        <th width="12%"></th>
	        <th width="12%"></th>
	        <th width="12%"></th>
	        <th width="12%"></th>
	    </tr>
	</thead>
	<tbody>
    	<tr style="height:14px;">
	    	<td></td>
	        <td>NAME:</td>
	        <td colspan="6">{{ $employeeSalary->employee->name }}</td>
	    </tr>
	   	<tr>
	   		<td></td>
	        <td>LOCATION:</td>
	        <td colspan="6">{{ $employeeSalary->job_location }}</td>
	    </tr>
	   	<tr>
	   		<td></td>
	        <td>PERIOD:</td>
	        <td colspan="6">{{ date('d F Y', strtotime($employeeSalary->start_date)) }} - {{ date('d F Y', strtotime($employeeSalary->end_date)) }}</td>
	    </tr>
    	<tr>
	        <td colspan="2"></td>
	        <td style="text-align:center;">W1</td>
	        <td style="text-align:center;">W2</td>
	        <td style="text-align:center;">W3</td>
	        <td style="text-align:center;">W4</td>
	        <td style="text-align:center;">W5</td>
	        <td style="text-align:center;">Additional Note</td>
	    </tr>
	    <tr>
	        <td colspan="2" style="text-align:center;"><b>MINIMUM COMPONENT</b></td>
	        <td colspan="6"></td>
	    </tr>
	    @foreach($employeeSalary->assessments as $key => $indicator)
	        <tr>
	            <td>{{ ($key + 1) }}</td>
	            <td>{{ $indicator->name }}</td>
	            <td style="text-align:center;">{{ number_format($additionalSalaryData['score_percentages_assessments'][$key]['week1'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($additionalSalaryData['score_percentages_assessments'][$key]['week2'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($additionalSalaryData['score_percentages_assessments'][$key]['week3'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($additionalSalaryData['score_percentages_assessments'][$key]['week4'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($additionalSalaryData['score_percentages_assessments'][$key]['week5'], 0) }}%</td>
	            <td>&nbsp;</td>
	        </tr>
	    @endforeach
	    <tr>
	        <td colspan="2" style="text-align:center;"><b>MINIMUM COMPONENT SCORE</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_assessments']['week1'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_assessments']['week2'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_assessments']['week3'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_assessments']['week4'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_assessments']['week5'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['average_minimum_component_score'], 2) }}%</b></td>
	    </tr>
	    <tr>
	    	<th colspan="8">
	        	<br/>
	        </td>
	    </tr>
	    <tr>
	        <td colspan="2" style="text-align:center;"><b>ADDITIONAL COMPONENT</b></td>
	        <td colspan="6"></td>
	    </tr>
	    @foreach($employeeSalary->achievements as $key => $achievement)
	        <tr>
	            <td>{{ ++$key }}</td>
	            <td>
		            @if ($key == 1)
					    Balance SKU Area
					@elseif ($key == 2)
					    %C National Achievement
					@elseif ($key == 3)
					    %EC National Achievement
					@elseif ($key == 4)
					    %Value National Achievement
					@elseif ($key == 5)
					    %C Area Achievement
					@elseif ($key == 6)
					    %EC Area Achievement
					@elseif ($key == 7)
					    Value Area Achievement
					@endif
		        </td>
	            <td style="text-align:center;">{{ number_format($achievement['week1'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($achievement['week2'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($achievement['week3'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($achievement['week4'], 0) }}%</td>
	            <td style="text-align:center;">{{ number_format($achievement['week5'], 0) }}%</td>
	            <td>&nbsp;</td>
	        </tr>
	    @endforeach
	    <tr>
	        <td colspan="2" style="text-align:center;"><b>ADDITIONAL COMPONENT SCORE</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_achievements']['week1'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_achievements']['week2'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_achievements']['week3'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_achievements']['week4'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($additionalSalaryData['total_achievements']['week5'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['average_additional_component_score'], 2) }}%</b></td>
	    </tr>
	    <tr>
	        <td colspan="2" style="text-align:center;"><b>FINAL SCORE</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['salary_final_score_week_1'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['salary_final_score_week_2'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['salary_final_score_week_3'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['salary_final_score_week_4'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['salary_final_score_week_5'], 2) }}%</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['average_final_score'], 2) }}%</b></td>
	    </tr>
	    <tr>
	    	<th colspan="8">
	        	<br/>
	        </td>
	    </tr>
	    <tr>
	        <td colspan="2">Minimum Component Amount</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['minimum_component_amount_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['minimum_component_amount_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['minimum_component_amount_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['minimum_component_amount_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['minimum_component_amount_week_5'], 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Additional Component Amount</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['additional_component_amount_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['additional_component_amount_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['additional_component_amount_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['additional_component_amount_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['additional_component_amount_week_5'], 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Total Amount</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['total_component_amount_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['total_component_amount_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['total_component_amount_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['total_component_amount_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['total_component_amount_week_5'], 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2"><b>Total Amount With Allowance</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_week_1'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_week_2'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_week_3'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_week_4'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_week_5'], 2) }}</b></td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Receivable Cut &gt; 60 Days</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->receivable_cut_60_days_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->receivable_cut_60_days_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->receivable_cut_60_days_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->receivable_cut_60_days_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->receivable_cut_60_days_week5, 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2"><b>Total Amount Received</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_week_1'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_week_2'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_week_3'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_week_4'], 2) }}</b></td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_week_5'], 2) }}</b></td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="6"></td>>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received'], 2) }}</b></td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2"><b>Maximum Amount Receivable</b></td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td style="text-align:center;"><b>{{ number_format($employeeSalary->maximum_salary_amount, 2) }}</b></td>
	        <td>If KPI 100%</td>
	    </tr>
	    <tr>
	        <td colspan="2"><b>Amount Received Difference</b></td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td>&nbsp;</td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_amount_received_difference'], 2) }}</b></td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Company Profit</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_week_5'], 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Overdue Receivable</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->overdue_receivable_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->overdue_receivable_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->overdue_receivable_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->overdue_receivable_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->overdue_receivable_week5, 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Payment From Marketing</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_marketing_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_marketing_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_marketing_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_marketing_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_marketing_week5, 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Payment From Sales</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_sales_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_sales_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_sales_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_sales_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_sales_week5, 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Payment From SPG</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_spg_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_spg_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_spg_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_spg_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->payment_from_spg_week5, 2) }}</td>
	        <td>&nbsp;</td>
	    </tr>
	    <tr>
	        <td colspan="2">Received Cash Payment</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->cash_payment_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->cash_payment_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->cash_payment_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->cash_payment_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->cash_payment_week5, 2) }}</td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_payment'], 2) }}</b></td>
	    </tr>
	    <tr>
	        <td colspan="2">Settlement Difference Minus Amount</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['settlement_difference_minus_amount_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['settlement_difference_minus_amount_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['settlement_difference_minus_amount_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['settlement_difference_minus_amount_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['settlement_difference_minus_amount_week_5'], 2) }}</td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_settlement_difference_minus_amount'], 2) }}</b></td>
	    </tr>
	    <tr>
	        <td colspan="2">Company Profit Difference Minus Amount</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_difference_minus_amount_week_1'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_difference_minus_amount_week_2'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_difference_minus_amount_week_3'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_difference_minus_amount_week_4'], 2) }}</td>
	        <td style="text-align:center;">{{ number_format($calculatedSalaryData['company_profit_difference_minus_amount_week_5'], 2) }}</td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_company_profit_difference_minus_amount'], 2) }}</b></td>
	    </tr>
	    <tr>
	        <td colspan="2">Weekly Sales</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->weekly_sales_week1, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->weekly_sales_week2, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->weekly_sales_week3, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->weekly_sales_week4, 2) }}</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->weekly_sales_week5, 2) }}</td>
	        <td style="text-align:center;"><b>{{ number_format($calculatedSalaryData['total_weekly_sales'], 2) }}</b></td>
	    </tr>
	    <tr>
	        <td colspan="2">WA Daily Report</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->wa_daily_report_week1, 2) }}%</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->wa_daily_report_week2, 2) }}%</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->wa_daily_report_week3, 2) }}%</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->wa_daily_report_week4, 2) }}%</td>
	        <td style="text-align:center;">{{ number_format($employeeSalary->wa_daily_report_week5, 2) }}%</td>
	        <td>&nbsp;</td>
	    </tr>
    </tbody>
</table>
