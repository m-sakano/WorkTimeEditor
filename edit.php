<?php
	require_once('config.php');
	require_once('createDynamoDBClient.php');
	require_once('getDynamoDBItem.php');
	session_start();

	// 未ログイン対応
	if (!isset($_SESSION['me'])) {
		header('Location: '.SITE_URL);
		exit;
	}
	// 日付設定
	if (isset($_POST['date'])) {
		$myDate = $_POST['date'];
	} 
	elseif (isset($_SESSION['date'])) {
		$myDate = $_SESSION['date'];
		unset($_SESSION['date']);
	} 
	else {
		header('Location: '.SITE_URL);
		exit;
	}
	
	$year =  mb_substr($myDate, 0, 4);
	$month = mb_substr($myDate, 4, 2);
	$day   = mb_substr($myDate, 6, 2);
	$dayOfWeek = date('D',strtotime($year.$month.$day));
	
	$client = createDynamoDBClient();
	$result = getDynamoDBItem($client, $_SESSION['email'],
		strtotime($year . $month . $day),
		strtotime('+1 day ' . $year . $month . $day)
		);
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
        <h3><?php echo $year . '/' . $month . '/' . $day . '&nbsp;' . $dayOfWeek ; ?></h3>
      </div>

      <h3>レコードの追加と更新</h3>
      <p class="help-block">登録済みのレコードと同じ時刻を入力すると上書き更新になります。*は必須項目です。</p>
      <form action="./update.php" method="post" data-toggle="validator">
      <input type="hidden" name="Date" value="<?php echo $myDate;?>">
      <div class="row">
      	<div class="col-sm-2">
		  <div class="form-group">
		    <label for="Hour">時*</label>
		    <input type="number" class="form-control" id="Hour" name="Hour" placeholder="0-23" min="0" max="23" required>
		    <div class="help-block with-errors"></div>
		  </div>
      	</div>
      	<div class="col-sm-2">
		  <div class="form-group">
		    <label for="Minute">分*</label>
		    <input type="number" class="form-control" id="Minute" name="Minute" placeholder="0-59" min="0" max="59" required>
		    <div class="help-block with-errors"></div>
		  </div>
      	</div>
      	<div class="col-sm-3">
		  <div class="form-group">
		    <label for="Attendance">ステータス*</label>
		    <select class="form-control" id="Attendance" name="Attendance" required>
		      <option value=""></option>
			  <option value="案件先出社">案件先出社</option>
			  <option value="案件先退社">案件先退社</option>
			  <option value="自社出社">自社出社</option>
			  <option value="自社退社">自社退社</option>
			</select>
		  </div>
      	</div>
      	<div class="col-sm-5">
		  <div class="form-group">
		    <label for="Description">備考</label>
		    <input type="text" class="form-control" id="Description" name="Description">
		  </div>
      	</div>
      </div>
      <button type="submit" class="btn btn-primary">レコードを追加 または 更新 <span class="glyphicon glyphicon-pencil" aria-hidden="true"></button>
      </form>

      <h3>レコードの確認と削除</h3>
	  <form action="./delete.php" method="post" data-toggle="validator">
	  <input type="hidden" name="Date" value="<?php echo $myDate;?>">
	  <div class="form-group">
      <table class="table table-striped">
      	<tr>
      		<th><span class="glyphicon glyphicon-check" aria-hidden="true"></span></th>
      		<th>時刻</th>
      		<th>ステータス</th>
      		<th>備考</th>
      	</tr>
      <?php if (iterator_count($result) > 0) { ?>
      <?php foreach ($result as $item) { ?>
      	<tr>
      		<td><input type="radio" name="UnixTime" id="<?php echo $item['UnixTime']['N']; ?>"
      				value="<?php echo $item['UnixTime']['N']; ?>" required></td>
      		<td><?php echo date('H:i', $item['UnixTime']['N']); ?></td>
      		<td><?php echo $item['Attendance']['S']; ?></td>
      		<td><?php echo $item['Description']['S']; ?></td>
      	</tr>
      <?php }} ?>
      </table>
      </div>
      <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#myModal" <?php if (iterator_count($result) == 0) { echo 'disabled'; }?>>
        選択したレコードの削除 <span class="glyphicon glyphicon-trash" aria-hidden="true"></span>
	  </button>
	  
		<!-- Modal -->
		<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">選択したレコードの削除</h4>
		      </div>
		      <div class="modal-body">
		        <p>レコードの削除は取り消せません。</p>
		        <p>削除を実行しますか？</p>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">キャンセル</button>
		        <button type="submit" class="btn btn-danger">削除を実行 <span class="glyphicon glyphicon-trash" aria-hidden="true"></span></button>
		      </div>
		    </div>
		  </div>
		</div>
		
      </form>
      
      <p class="text-right">
        <a href="./">
        <?php 
        	$_SESSION['year'] = $year;
        	$_SESSION['month'] = $month;
        ?>
	    <?php echo $year . '年' . $month . '月の一覧に戻る'; ?>
	    <span class="glyphicon glyphicon-play" aria-hidden="true"></span>
	    </a>
	  </p>

      <footer class="footer">
      <div align="center">
        <p><a href="https://github.com/m-sakano/WorkTimeEditor">WorkTimeEditor</a></p>
      </div>
      </footer>

    </div> <!-- /container -->


    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="bootstrap/docs/assets/js/ie10-viewport-bug-workaround.js"></script>
    
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="jquery/jquery.min.js"></script>
    <script src="bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="bootstrap-validator/dist/validator.min.js"></script>
  </body>
</html>
