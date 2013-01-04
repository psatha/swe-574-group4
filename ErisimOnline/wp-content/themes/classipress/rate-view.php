<?php

Session_start();
include("rate-settings.php"); //INCLUDES THE IMPORTANT MySQL SETTINGS

//$q = mysql_query("SELECT * FROM $content WHERE id='$id'"); //GETS THE CONTENT ID
//$r = mysql_fetch_assoc($q);
//$con = $r["content"]; //CONTENT OF THE ID
//$id = $r["id"]; //NEW ID VARIABLE, USED TO CHECK IF IT'S IN THE DATABASE
$id = $_SESSION['av_id'];

?>
<script src="http://code.jquery.com/jquery-latest.js"></script>
<script>
function rate(rating){ //'rating' VARIABLE FROM THE FORM in view.php
var data = 'rating='+rating+'&id=<?php echo $id; ?>';

$.ajax({
type: 'POST',
url: 'rate.php',
data: data,
success: function(e){
$("#ratings").html(e);
}
});
}
</script>
<style>
/*GIVES THE BUTTONS AND CANCEL LINK THE POINTER ON MOUSEOVER*/
#solved, #unsolved, #cancel {
cursor: pointer;
}
/*GIVES THE CANCEL BUTTON AN UNDERLINE ON MOUSEOVER*/
#cancel:hover {
text-decoration: underline;
}
</style>
<?php

//IF $id EXISTS, THEN COUNT LIKES & DISLIKES
if($id){
    //COUNTS THE TOTAL NUMBER OF LIKES &amp; DISLIKES
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
    
    //EVERYTHING HERE DISPLAYED IN HTML
    echo '<div id="ratings">'.$m.'</div>';
}
else
{
echo "Invalid ID";
}

?>