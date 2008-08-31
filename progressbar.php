<?php
/*
 * Copyright (C) 2007  Joshua Eichorn
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * I This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 */
// HTML_AJAX_Server class
require_once 'HTML/AJAX/Server.php';

// UploadProgressMeter class were exporting
require_once './includes/UploadProgressMeterStatus.class.php';

$server = new HTML_AJAX_Server();

$status = new UploadProgressMeterStatus();

// were registering class and method name by hand so we don't run into php4/5 case compat problems
$server->registerClass($status,'UploadProgressMeterStatus',array('getStatus'));

$server->handleRequest();
?>
