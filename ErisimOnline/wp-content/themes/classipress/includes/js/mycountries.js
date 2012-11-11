/*
	*	Author : Shafiul Azam								*
	*	ishafiul@gmail.com
        *	Version 2.0
        *	Update: Now works in MSIE!									*
	*	Description:										*
	*	Inserts Countries and/or States as Dropdown List	*
	*	How to Use:											*
	*														*
			In Head section:
			<script type= "text/javascript" src = "countries.js"></script>
			In Body Section:
			Select Country:   <select onchange="print_state('state',this.selectedIndex);" id="country" name ="country"></select>
			<br />
			City/District/State: <select name ="state" id ="state"></select>
			<script language="javascript">print_country("country");</script>	
	*
	*	License: OpenSource, Permission for modificatin Granted, KEEP AUTHOR INFORMATION INTACT
	*	Aurthor's Website: http://shafiul.progmaatic.com
	*
*/
var country_arr = new Array("istanbul","ankara","izmir");

var s_a = new Array();
s_a[0]="";
s_a[1]="Adalar|Arnavutkoy|Atasehir|Avcilar|Bagcilar|Bahcelievler|Bakirkoy|Basaksehir|Bayrampasa|Besiktas|Beykoz|Beylikduzu|Beyoglu|Buyukcekmece|Catalca|Cekmekoy|Esenler|Esenyurt|Eyup|Fatih|Gaziosmanpasa|Gungoren|Kadikoy|Kagithane|Kartal|Kucukcekmece|Maltepe|Pendik|Sancaktepe|Sariyer|Silivri|Sultanbeyli|Sultangazi|Sile|Sisli|Tuzla|Umraniye|Uskudar|Zeytinburnu";
s_a[2]="Akyurt|Altindag|Ayas|Bala|Beypazari|Camlidere|Cankaya|Cubuk|Elmadag|Etimesgut|Evren|Golbasi|Gudul|Kalecik|Kazan|Kecioren|Mamak|Nallihan|Polatli|Pursaklar|Sincan|Yenimahalle";
s_a[3]="Aliaga|Balcova|Bayindir|Bayrakli|Bornova|Buca|Cigli|Foca|Gaziemir|Guzelbahce|Karabaglar|Karsiyaka|Kemalpasa|Konak|Menderes|Menemen|Narlidere|Seferihisar|Selcuk|Torbali|Urla";

function print_country(country_id){
	// given the id of the <select> tag as function argument, it inserts <option> tags
	var option_str = document.getElementById(country_id);
	var x, i=0;
	for(x in country_arr){
		option_str.options[i++] = new Option(country_arr[x],country_arr[x]);
	}
}

function print_state(state_id, state_index){
	var option_str = document.getElementById(state_id);
	var x, i=0; state_index++;
	var state_arr = s_a[state_index].split("|");
	for(x in state_arr){
            option_str.options[i++] = new Option(state_arr[x],state_arr[x]);
	}
}

function set_country(country_id,state_id,country_index){
	var cp_country = document.getElementById(country_id);
	var option_str = document.getElementById(state_id);
	cp_country.selectedIndex = country_index;
	print_state(state_id,country_index);
}
	
/*	Author's Blog: bdhacker.wordpress.com	*/  