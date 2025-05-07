<?php
$host = "localhost";
$db = "da_cnm";
$user = "root";
$pass = "";


class dl{
    public function connect(){
        $con = mysqli_connect("localhost","root","","da_cnm");
        $con -> set_charset("utf8");
        return $con;
    }
    public function layDL($sql){
        $l = $this -> connect();
        $kq = mysqli_query($l,$sql);
        if(mysqli_num_rows($kq) >0){
            $arr = array();
            while($i =mysqli_fetch_assoc($kq)){
                $sdt = $i['sdt'];
                $pw = $i['pw'];
                $arr[] = array("sdt"=>$sdt,"pw"=>$pw);
            }
            header("content-type:application/json,charset=utf-8");
            echo json_encode($arr);
        }
        else{
            echo "ko cos";
        }
    }
}
?>
