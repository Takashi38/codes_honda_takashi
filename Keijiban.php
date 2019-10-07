<html>
<meta charset = “UFT-8”>
<h1>簡易掲示板</h1><br>
<h2>投稿・編集用フォーム</h2>
<form method="POST" action="">
	<p>編集<input type = "checkbox" name = "check" value = 1>編集番号<input type = "number" name = "editNumber"></p>
	<p>お名前：<input type="text" name="name"></p>
	<p>コメント：<textarea name="comment"></textarea></p>
	<p>パスワード:<input type = "text" name ="epass"></p>
	<p><input type="submit" value="送信"></p>
</form>
<form method = "POST" action = "">
	<h2>削除用フォーム</h2>
	<p>削除番号:<input type ="number" name = "deleteNumber"></p>
	<p>パスワード:<input type = "text" name = "dpass"><b><input type = "submit" value = "削除"></p>
</form>
<?php

//日時設定等
	$date = date("Y/n/j G:i:s");
	$con = new Contents();
	$container = new Container();

//------------書き込み時の処理-------------//

	if(isset($_POST['comment'])){
		if(!empty($_POST['name']) && !empty($_POST['comment']) && empty($_POST['check'])){
//コンテンツ登録
			$con = new Contents($_POST['name'], $_POST['comment'], $date);
//password登録
			if(isset($_POST['epass'])){
				$con -> setPass($_POST['epass']);
			}
//配列に格納
			$number = end($container -> getContainer()) -> getNumber() + 1;
			$con -> setNumber($number);
			$container -> add($con);
			echo "コメント内容　「". $con -> getComment(). "」を受け付けました<hr>";
		}
	}

//------------削除時の処理-------------//

	if(isset($_POST['deleteNumber'])){
		$deleteNumber = $_POST['deleteNumber'];
//Contentsはコメントの配列、Contentには後に配列の要素を定義
		$contents = $container -> getContainer();
		if($deleteNumber <= count($contents) && $deleteNumber >= 1 ){
//削除できるか判定
			if(isset($_POST['dpass'])){ 
				$pass = $_POST['dpass'];
				$content = $contents[$deleteNumber - 1];
//削除処理
				if($content-> checkPass($pass)){
					$content -> cDelete();
					$container -> update($content);
					echo "<p>指定された投稿を削除しました。</p>";
				}else{
					echo "<p>パスワードが間違っています。削除できませんでした。</p>";
				}
			}
		}else{
			echo "<p>不正な番号が入力されました。</p>";
		}
	}
//------------編集時の処理-------------//

	if(isset($_POST['editNumber']) && !empty($_POST['check'])){
		if($_POST['check'] == 1){
			$editNumber = $_POST['editNumber'];
			$editName = $_POST['name'];
			$editComment = $_POST['comment'];
//Contentsはコメントの配列、Contentには後に配列の要素を定義 
			$contents = $container -> getContainer();
			if($editNumber <= count($contents) && $editNumber >=1 ){
						
//編集できるか判定
				if(isset($_POST['epass'])){
					$pass = $_POST['epass']; 
					$content = $contents[$editNumber - 1];
					if($content -> checkPass($pass)){
						$content -> setName($editName);
						$content -> setComment($editComment);
						$content -> cEdited();
						$container -> update($content);
					}else{
						echo "<p>パスワードが間違っています。編集できません。</p>";
					}
				}
			}else{
				echo "<p>編集の番号が不正に入力されました。</p>";
			}
		}
	}

//最後にループで内容を出力
if(count($container -> getContainer()) >= 1){
	$container -> output();
}
?>
<?php
	$dsn = 'mysql:dbname=********;host=localhost;charset=utf8';
	$user = '*********';
	$password = '*********';
	$pdo = new PDO($dsn, $user, $password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
	$sql = "CREATE TABLE IF NOT EXISTS Container". " (". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(32),".  "comment TEXT,". "date TEXT,". "pass TEXT". ");";
	$stmt = $pdo->query($sql);


//ーーーーーーーーーーーーーーーーーーーーーーーーーーーコメントを格納しておくクラス


Class Container{
//接続用の諸々
	private $dsn = 'mysql:dbname=********;host=localhost;charset=utf8';
	private $user = '********';
	private $password = '*********';
	

	private $container = array();

//コンストラクタ
	public function __construct(){
	
		$pdo = new PDO($this -> dsn, $this -> user, $this -> password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
		$sql = "CREATE TABLE IF NOT EXISTS Container". " (". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(32),".  "comment TEXT,". "date TEXT,". "pass TEXT". ");";
		$stmt = $pdo->query($sql);
//実際の処理
		$sql = 'SELECT * FROM Container';
		$stmt = $pdo->query($sql);
		$results = $stmt->fetchAll();
		foreach ($results as $row){
			$content = new Contents($row['name'], $row['comment'], $row['date'], $row['id']);
			$content -> setPass($row['pass']);
			$this -> container[] =$content;
		}
	}

//コメントを表に格納 
	public function add(Contents $contents){

		$pdo = new PDO($this -> dsn, $this -> user, $this -> password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
		$sql = "CREATE TABLE IF NOT EXISTS Container". " (". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(32),".  "comment TEXT,". "date TEXT,". "pass TEXT". ");";
		$stmt = $pdo->query($sql);
//実際の処理
		$sql = 'INSERT INTO Container (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)';
		$stmt = $pdo -> prepare($sql);
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt -> bindParam(':date', $date, PDO::PARAM_STR);
		$stmt -> bindParam(':pass', $pass, PDO::PARAM_STR);
		$name = $contents -> getName();
		$comment = $contents -> getComment();
		$date = $contents -> getDate();
		$pass = $contents -> getPass();
		$stmt -> execute();
		$this -> container[] = $contents;
	}

//表の編集
	public function update(Contents $content){
	
		$pdo = new PDO($this -> dsn, $this -> user, $this -> password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
		$sql = "CREATE TABLE IF NOT EXISTS Container". " (". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(32),".  "comment TEXT,". "date TEXT,". "pass TEXT". ");";
		$stmt = $pdo->query($sql);
//実際の処理
		$sql = 'update Container set name=:name,comment=:comment,date=:date where id=:id';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':name', $editName, PDO::PARAM_STR);
		$stmt->bindParam(':comment', $editComment, PDO::PARAM_STR);
		$stmt->bindParam(':id', $editId, PDO::PARAM_INT);
		$stmt->bindParam(':date', $editDate, PDO::PARAM_STR);
		$editName = $content -> getName();
		$editComment = $content -> getComment();
		$editId = $content -> getNumber();
		$editDate = date("Y/n/j G:i:s");
		$stmt->execute();
	}

//コメント配列を返す
	public function getContainer(){
		return $this -> container;
	}
	
//表の内容を出力
	public function output(){
		$pdo = new PDO($this -> dsn, $this -> user, $this -> password,[PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING]);
		$sql = "CREATE TABLE IF NOT EXISTS Container". " (". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(32),".  "comment TEXT,". "date TEXT,". "pass TEXT". ");";
		$stmt = $pdo->query($sql);
//実際の処理
		foreach ($this ->container as $contents){
			echo $contents -> toString(). "<hr>";
		}			
	}
}
?>

<?php
//ーーーーーーーーーーーーーーーーーーーーーーーー名前・コメント内容のクラス
class Contents{

	private $name;
	private $comment;
	private $date;
	private $number = 1;
	private $pass = "";

	public function __construct($name = "", $comment = "", $date = "", $number = ""){
		$this -> name = $name;
		$this -> comment = $comment;
		$this -> date = $date;
		$this -> number = $number;
	}

//番号取得・設定
	public function getNumber(){
		return $this -> number;
	}
	public function setNumber($n){
		if($n != ""){
		$this -> number = $n;
		}
	}
//名前取得・変更
	public function getName(){
		return $this -> name;
	}
	public function setName($newName){
		if($newName != ""){
		$this -> name = $newName;
		}
	}
//コメント内容取得・変更・編集時の処理
	public function getComment(){
		return $this -> comment;
	}
	public function setComment($newComment){
		if($newComment != ""){
		$this -> comment = $newComment;
		}
	}
	public function cEdited(){
		$this -> comment = $this -> comment. "　　(編集済み)";
	}
//日付取得
	public function getDate(){
		return $this -> date;
	}

//パスワード設定・確認・取得
	public function setPass($password){
		$this -> pass = $password;
	}
	public function checkPass($p){
		if($this -> pass == $p){
			return true;
		}else{
			return false;
		}
	}
	public function getPass(){
		return $this -> pass;
	}
//コメント削除
	public function cDelete(){
		$this -> name = " ";
		$this -> comment ="（削除されました）　";
		$this -> date = date("Y/n/j G:i:s");
	}

//文字列として出力
	public function toString(){
			return $this -> number. ":名前:". $this -> name. "  ". $this -> comment. "   ". $this -> date;
	}
}
?>
</html>
