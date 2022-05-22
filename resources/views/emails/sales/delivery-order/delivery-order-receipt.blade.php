<style>
.receipt-container {
  width: 100%;
  padding: 30px;
}
.table-items {
  border: solid 1px black;
  margin-top: 10px;
  margin-bottom: 10px;
}
table.table-items, .table-items th, .table-items td {
  border: 1px solid black;
  border-collapse: collapse;
}
.table-items th, .table-items td {
  padding: 5px;
}
.my-5px {
  margin: 5px 0 5px 0;
}
.receipt-detail {
  min-width: 200px;
  max-width: 250px;
  display: inline;
  float: right;
}
.header-divider {
  height:10px;
  border:none;
  color:gray;
  background-color:gray;
}
.watermark{
  position: fixed;
  top: 0px;
  left: 0px;
  height: 100%;
  width: 100%;
}
</style>

@php
//parse if data is array, it happen when view load from app/Http/Controllers/Api/EmailServiceController.php@send
if(gettype($deliveryOrder) === 'array') {
  $deliveryOrder = (object) $deliveryOrder;
  $deliveryOrder->form = (object) $deliveryOrder->form;
  $deliveryOrder->warehouse = (object) $deliveryOrder->warehouse;
  $deliveryOrder->customer = (object) $deliveryOrder->customer;
  $deliveryOrder->salesOrder = (object) $deliveryOrder->sales_order;
  $deliveryOrder->salesOrder->form = (object) $deliveryOrder->salesOrder->form;

  $deliveryOrder->items = collect($deliveryOrder->items)->map(function ($item) {
    $item['item'] = (object) $item['item'];
    return (object) $item;
  });

  $deliveryOrder->form->createdBy = (object) $deliveryOrder->form->created_by;
  $deliveryOrder->form->requestApprovalTo = (object) $deliveryOrder->form->request_approval_to;
}
@endphp

@if($deliveryOrder->form->cancellation_status === 1)
<div class="watermark">
  <img 
    src="{{ $draftimg ?? url('/img/draft-watermark.png') }}" 
    style="opacity: 0.5; display: block; margin: 15% auto 0px; width: 600px"
  >
</div>
@endif

<table class="receipt-container m-2 mb-4 mx-auto">
  <thead>
    <tr>
      <td>
        <div style="margin-bottom: 10px;">
          <div style="display: inline; width: 110px; height: 110px; align-self: center;">
            <img src="{{ $logo ?? url('/img/logo.png') }}" alt="Logo" style="width: 100px; height: 100px;">
          </div>
          <div class="receipt-detail">
            <h1 style="margin-top: 0; margin-bottom: 5px;">
              Delivery Order
            </h1>
            <h3
              class="my-5px"
              style="line-height: 22px"
            >
              {{ $tenant->name }}
            </h3>
            <p
              class="my-5px"
              style="line-height: 15px;"
            >
              {{ $tenant->address }}
            </p>
            <p class="my-5px">
              {{ $tenant->phone }}
            </p>
          </div>
        </div>
        <hr class="header-divider">
        <div style="margin-top: 10px; margin-bottom: 10px">
        <table width="100%">
          <tr>
            <td valign="top">
              <table class="header-detail" style="margin-right: 20px;">
                <tr>
                  <td>Form Number</td>
                  <td>:</td>
                  <td>{{ $deliveryOrder->form->number }}</td>
                </tr>
                <tr>
                  <td>Date</td>
                  <td>:</td>
                  <td>{{ date('d M Y', strtotime($deliveryOrder->form->date)) }}</td>
                </tr>
                <tr>
                  <td>Sales Order</td>
                  <td>:</td>
                  <td>{{ optional($deliveryOrder->salesOrder)->form->number }}</td>
                </tr>
                <tr>
                  <td>Warehouse</td>
                  <td>:</td>
                  <td>{{ optional($deliveryOrder->warehouse)->name }}</td>
                </tr>
              </table>
            </td>
            <td valign="top" align="right">
              <table
                class="header-detail"
                style="margin-left: 20px;"
              >
                <tr>
                  <td>Customer</td>
                  <td>:</td>
                  <td>{{ optional($deliveryOrder->customer)->name }}</td>
                </tr>
                <tr>
                  <td>Address</td>
                  <td>:</td>
                  <td>{{ optional($deliveryOrder->customer)->address }}</td>
                </tr>
                <tr>
                  <td>Phone number</td>
                  <td>:</td>
                  <td>{{ optional($deliveryOrder->customer)->phone }}</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        </div>
      </td>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>
        <table
          class="table-items"
          style="width: 100%;"
        >
          <thead>
            <tr>
              <th class="text-center">
                Item
              </th>
              <th class="text-center">
                Quantity Requested
              </th>
              <th class="text-center">
                Quantity Delivered
              </th>
            </tr>
          </thead>
          <tbody>
            @foreach($deliveryOrder->items as $deliveryOrderItem)
            <tr>
              <td>
                {{ $deliveryOrderItem->item->label }}
              </td>
              <td class="text-center">
                {{ round($deliveryOrderItem->quantity_requested, 2) }} {{ $deliveryOrderItem->unit }}
              </td>
              <td class="text-center">
                {{ round($deliveryOrderItem->quantity_delivered, 2) }} {{ $deliveryOrderItem->unit }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
        <div style="margin-top: 75px;">
          <div class="text-center" style="display: inline; float: right;">
            <h3>Approved By</h3>
            <br><br><br>
            {{ $deliveryOrder->form->requestApprovalTo->full_name ?? $deliveryOrder->form->requestApprovalTo->getFullNameAttribute() }}
          </div>
          <div class="text-center" style="display: inline; float: right; margin-right: 75px;">
            <h3>Created By</h3>
            <br><br><br>
            {{ $deliveryOrder->form->createdBy->full_name ?? $deliveryOrder->form->createdBy->getFullNameAttribute() }}
          </div>
          <div style="clear: both"></div>
        </div>
      </td>
    </tr>
  </tbody>
</table>