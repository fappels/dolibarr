<?php
/* Copyright (C) 2010-2011 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2017      Francis Appels <francis.appels@yahoo.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>

<!-- BEGIN PHP TEMPLATE -->

<?php
global $langs;

$langs->load("products");

$warehouseSelect = $formproduct->selectWarehouses(19, "scan_warehouse", '', 1, 0);

echo <<<HTML
<div id="dialog-form" title="Scan Barcode">

<form>
  <fieldset>
    <input type="text" name="scan_result" id="scan_result" disabled="true" value="Please scan Barcode" class="ui-widget-content ui-corner-all">
    <label for="scan_barcode" style="display:block;">Product</label>
    <input type="text" name="scan_barcode" id="scan_barcode" class="ui-widget-content ui-corner-all">
    <label for="scan_warehouse" style="display:block;">Warehouse</label>
    $warehouseSelect
    <label for="scan_qty" style="display:block;">Quantity</label>
    <input type="text" name="scan_qty" id="scan_qty" class="ui-widget-content ui-corner-all" value="1">
    <!-- Allow form submission with keyboard without duplicating the dialog button -->
    <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
  </fieldset>
</form>

</div>
HTML
?>
<!-- END PHP TEMPLATE -->
