<?php
$id = $id = $_POST["id"];
$rating = $_POST["rating"];
$rating_type = array("solved", "unsolved");

if(in_array($rating, $rating_type)){
    
    include("rate-settings.php"); //INCLUDES THE IMPORTANT SETTINGS
    
    //CHECKS IF $id EXISTS
    $q = mysql_query("SELECT * FROM $content WHERE ID='$id'");
    $r = mysql_fetch_assoc($q);
    $id = $r["ID"]; //NEW ID VARIABLE, USED TO CHECK IF IT'S IN THE DATABASE
    
    //COUNTS LIKES & DISLIKES IF $id EXISTS
    if($id)
    {
        //CHECKS IF USER HAS ALREADY RATED CONTENT
        $q = mysql_query("SELECT * FROM $ratings WHERE id='$id' AND ip='$ip'");
        $r = mysql_fetch_assoc($q); //CHECKS IF USER HAS ALREADY RATED THIS ITEM
        
        //IF USER HAS ALREADY RATED
        if($r["rating"]){
            if($r["rating"]==$rating){
                mysql_query("DELETE FROM er_av_rating WHERE id='$id' AND ip='$ip'"); //DELETES RATING
            } else {
                mysql_query("UPDATE er_av_rating SET rating='$rating' WHERE id='$id' AND ip='$ip'"); //CHANGES RATING
            }
        } else {
            mysql_query("INSERT INTO er_av_rating VALUES('$rating','$id','$ip')"); //INSERTS INITIAL RATING
        }
        
        //COUNT LIKES & DISLIKES
        $q = mysql_query("SELECT * FROM $ratings WHERE id='$id' AND rating='solved'");
        $solved = mysql_num_rows($q);
        $q = mysql_query("SELECT * FROM $ratings WHERE id='$id' AND rating='unsolved'");
        $unsolved = mysql_num_rows($q);
        
        //LIKE & DISLIKE IMAGES
        $l = 'http://wcetdesigns.com/images/buttons/l_color.png';
        $d = 'http://wcetdesigns.com/images/buttons/d_color.png';
        
        //CHECKS IF USER HAS ALREADY RATED CONTENT
        $q = mysql_query("SELECT * FROM $ratings WHERE id='$id' AND ip='$ip'");
        $r = mysql_fetch_assoc($q); //CHECKS IF USER HAS ALREADY RATED THIS ITEM
        
        //IF SO, THE RATING WILL HAVE A SHADOW
        if($r["rating"]=="solved"){
            $l = 'http://wcetdesigns.com/images/buttons/l_color_shadow.png';
        }
        if($r["rating"]=="unsolved"){
            $d = 'http://wcetdesigns.com/images/buttons/d_color_shadow.png';
        }
        
        //FORM & THE NUMBER OF LIKES & DISLIKES
        $m = '<img title="Cozuldu" id="solved" onClick="rate($(this).attr(\'id\'))" src="'.$l.'"> '.$solved.' &nbsp;&nbsp; <img title="Cozulmedi" id="unsolved" onClick="rate($(this).attr(\'id\'))" src="'.$d.'"> '.$unsolved;
    
        //EVERYTHING HERE DISPLAYED IN HTML AND THE "ratings" ELEMENT FOR AJAX
        echo $m;
    }
    else
    {
    echo "Invalid ID";
    }
}

?>