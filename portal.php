<?php
	require_once('config.php');
	require_once('createDynamoDBClient.php');
	require_once('getDynamoDBItem.php');
	session_start();

	// 対象月 ... defaultは現在時刻の月。$_POST['year'], $_POST['month] がある場合はその月。
	if (isset($_POST['year']) && isset($_POST['month'])) {
		$year = $_POST['year'];
		$month = sprintf("%02d",$_POST['month']);
	} 
	// $_SESSION['year'], $_SESSION['month']がある場合はその月
	elseif (isset($_SESSION['year']) && isset($_SESSION['month'])) {
		$year = $_SESSION['year'];
		$month = $_SESSION['month'];
		unset($_SESSION['year']);
		unset($_SESSION['month']);
	}
	else {
		$year = date('Y', time());
		$month = date('m', time());
	}

	$client = createDynamoDBClient();
	$result = getDynamoDBItem($client, $_SESSION['email'],
		strtotime($year . $month . '01'),
		strtotime('+1 month ' . $year . $month . '01')
		);
	

	for ($i = 1; true; $i++) {
		if ($i > (int)date('d',strtotime('last day of ' . $year . $month . '01'))) break;
		$attendance[$i]['day'] = date('d D', strtotime($year . $month . sprintf('%\'.02d',$i) ));
		if (iterator_count($result) > 0) {
			foreach ($result as $item) {
				if ((int)date('d', $item['UnixTime']['N']) == $i) {
					switch ($item['Attendance']['S']) {
						case '自社出社':
							if (isset($attendance[$i]['attendanceOn'])) {
								$attendance[$i]['attendanceOn'] .= '<br>' . date('H:i', $item['UnixTime']['N']);
							} else {
								$attendance[$i]['attendanceOn'] = date('H:i', $item['UnixTime']['N']);	
							}
							break;
						case '自社退社':
							if (isset($attendance[$i]['attendanceOff'])) {
								$attendance[$i]['attendanceOff'] .= '<br>' . date('H:i', $item['UnixTime']['N']);
							} else {
								$attendance[$i]['attendanceOff'] = date('H:i', $item['UnixTime']['N']);
							}
							break;
						case '案件先出社':
							if (isset($attendance[$i]['attendanceCustomerOn'])) {
								$attendance[$i]['attendanceCustomerOn'] .= '<br>' . date('H:i', $item['UnixTime']['N']);
							} else {
								$attendance[$i]['attendanceCustomerOn'] = date('H:i', $item['UnixTime']['N']);
							}
							break;
						case '案件先退社':
							if (isset($attendance[$i]['attendanceCustomerOff'])) {
								$attendance[$i]['attendanceCustomerOff'] .= '<br>' . date('H:i', $item['UnixTime']['N']);
							} else {
								$attendance[$i]['attendanceCustomerOff'] = date('H:i', $item['UnixTime']['N']);
							}
							break;
						default:
					}
					if (isset($attendance[$i]['description'])) {
						if (isset($item['Description']['S'])) {
							$attendance[$i]['description'] .= '<br>' . $item['Description']['S'];
						}
					} else {
						if (isset($item['Description']['S'])) {
							$attendance[$i]['description'] =  $item['Description']['S'];
						}
					}
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title><?php echo BRAND; ?></title>

    <!-- Bootstrap core CSS -->
    <link href="bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="jumbotron-narrow.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy these 2 lines! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->
    <script src="bootstrap/docs/assets/js/ie-emulation-modes-warning.js"></script>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
  <?php include_once("analyticstracking.php") ?>

    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-pills pull-right">
          	<li>
          		<a href="logout.php" role="button" aria-haspopup="true" aria-expanded="false"><img src="<?php echo $_SESSION['picture'];?>" height="32px" width="32px" class="img-circle"> ログアウト</a>
            </li>
          </ul>
        </nav>
        <h4 class="text-muted"><?php echo BRAND;?> <span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></h4>
      </div>
	
      <div class="jumbotron">
        <h3><?php echo $year . '年' . $month . '月'; ?></h3>
      </div>
    
	<div class="row">
	  <form action="./" method="post" data-toggle="validator">
	  <div class="col-sm-4"></div>
	  <div class="form-group col-sm-8">
	    <div class="input-group">
		    <input type="number" class="form-control" id="Year" name="year" placeholder="YYYY" value="<?php echo $year;?>" min="1970" max="9999" required>
		    <div class="input-group-addon">年</div>
		    <input type="number" class="form-control" id="Month" name="month" placeholder="MM" value="<?php echo $month;?>" min="1" max="12" required>
		    <div class="input-group-addon">月</div>
		    <span class="input-group-btn">
	    	  <button type="submit" class="btn btn-default">表示する月の変更 <span class="glyphicon glyphicon-repeat" aria-hidden="true"></button>
	  		</span>
	  	</div>
	  	<div class="help-block with-errors"></div>
	  </div>
	  </form>
	</div>

	  <form action="./edit.php" method="post" data-toggle="validator">
      <table class="table table-striped">
      	<tr>
      		<th><span class="glyphicon glyphicon-check" aria-hidden="true"></span></th>
      		<th>日付</th>
      		<th>案件先出社</th>
      		<th>案件先退社</th>
      		<th>自社出社</th>
      		<th>自社退社</th>
      		<th>備考</th>
      	</tr>
      <?php foreach ($attendance as $row) { ?>
      	<tr>
      		<td><input type="radio" name="date" id="date<?php echo $row['day']; ?>"
      				value="<?php echo $year . $month . sprintf('%\'.02d',$row['day']); ?>" required></td>
      		<td><?php echo $row['day']; ?></td>
      		<td><?php echo $row['attendanceCustomerOn']; ?></td>
      		<td><?php echo $row['attendanceCustomerOff']; ?></td>
      		<td><?php echo $row['attendanceOn']; ?></td>
      		<td><?php echo $row['attendanceOff']; ?></td>
      		<td><?php echo $row['description']; ?></td>
      	</tr>
      <?php } ?>
      </table>
      <button type="submit" class="btn btn-primary">選択したレコードの修正 <span class="glyphicon glyphicon-pencil" aria-hidden="true"></button>
      </form>

      <footer class="footer">
      <div align="center">
        <p><a href="https://github.com/m-sakano/WorkTimeEditor">WorkTimeEditor</a></p>
      </div>
      </footer>

    </div> <!-- /container -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="jquery/jquery.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bootstrap-validator/dist/validator.min.js"></script>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
  </body>
</html>
