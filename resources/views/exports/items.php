<table>
 <tr>
  <td>Date Export</td>
  <td><?=date('d F Y H:i')?></td>
  <?php for($i=1;$i<=13;$i++) echo "<td></td>"; ?>
 </tr>
 <tr><?php for($i=1;$i<=15;$i++) echo "<td></td>"; ?></tr>
 <tr>
  <td></td><td>Item Code</td><td>Item name</td>
  <td>Chart of Account</td><td>Uom</td><td>Converter</td>
  <td>Uom</td><td>Converter</td><td>Uom</td><td>Converter</td>
  <td>Expiry Date</td><td>Production Number</td><td>Default Purchase</td>
  <td>Default Sales</td><td>Group</td>
 </tr>
 <?php foreach($items as $i): ?>
 <tr>
  <td></td><td><?=$i->code?></td><td><?=$i->name?></td>
  <td><?=$i->account_alias?></td>
  <td><?=(isset($i->listsatuan[0])?$i->listsatuan[0]->name:'')?></td>
  <td><?=(isset($i->listsatuan[0])?intval($i->listsatuan[0]->converter):'')?></td>
  <td><?=(isset($i->listsatuan[1])?$i->listsatuan[1]->name:'')?></td>
  <td><?=(isset($i->listsatuan[1])?floatval($i->listsatuan[1]->converter):'')?></td>
  <td><?=(isset($i->listsatuan[2])?$i->listsatuan[2]->name:'')?></td>
  <td><?=(isset($i->listsatuan[2])?floatval($i->listsatuan[2]->converter):'')?></td>
  <td><?=($i->require_expiry_date==1?'true':'false')?></td><td><?=($i->require_production_number==1?'true':'false')?></td>
  <td><?=$i->def_purchase_alias?></td><td><?=$i->def_sales_alias?></td><td><?=$i->group_name?></td>
 </tr>
 <?php endforeach; ?>
</table>