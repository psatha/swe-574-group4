package com.example.engelsizsiniz;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import overlay.ItemOverLayForSearch;
import overlay.ItemOverlay;
import overlay.ItemOverlayForGet;

import com.example.engelsizsiniz.Show_AV.spinnerSet;
import com.google.android.maps.GeoPoint;
import com.google.android.maps.MapActivity;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.Overlay;
import com.google.android.maps.OverlayItem;

import android.graphics.drawable.Drawable;
import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.view.Gravity;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

public class Search_AV extends MapActivity {

	//map values
	protected LocationManager locationManager;
	protected Location location;
	protected GeoPoint point;
	protected Button retrieveLocationButton;
	protected MapView mapView;
	protected MapController mapController;
	protected static ItemOverlayForGet itemoverlay;
	protected static ItemOverLayForSearch itemoverlay2;
	protected static List<Overlay> mapOverlays;

	public static String note;
	public static String title;
	public static int ID;
	public static String adresInfo;
	public static EditText adres;

	private static String url_Pos = "http://swe.cmpe.boun.edu.tr/fall2012g4/getAllPos.php";
	private static String url_listAllAV = "http://swe.cmpe.boun.edu.tr/fall2012g4/allAV.php";

	JSONParser jsonParser = new JSONParser();
	public ProgressDialog pDialog;
	public JSONArray products = null;

	static double langitude, latitude;
	static String category;
	static String guid;

	public static ArrayList<AV> allAV = new ArrayList<AV>();

	public static Button searchButton;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		new LoadAllProducts().execute();
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_search__av);
		defineGUI();
		setListeners();
		setMap();
		showCurrentLocation();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.activity_search__av, menu);
		return true;
	}

	protected void setMap ()
	{

		try {

			locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);

			//get first location
			Criteria criteria = new Criteria();
			String provider = locationManager.getBestProvider(criteria, true);
			//locationManager.requestSingleUpdate(provider, mylocationListener);

			location = locationManager.getLastKnownLocation(provider);

			mapView = (MapView) findViewById(R.id.mapview);
			mapView.setBuiltInZoomControls(true);
			mapController = mapView.getController();

			mapOverlays = mapView.getOverlays();
			Drawable drawable = this.getResources().getDrawable(R.drawable.androidmarker);
			Drawable drawable2 = this.getResources().getDrawable(R.drawable.marker);
			itemoverlay = new ItemOverlayForGet(drawable, this);
			itemoverlay2 = new ItemOverLayForSearch(drawable2, this);
		} catch (NotFoundException e) {
			Toast.makeText(Search_AV.this, "Lütfen Baðlantý Ayarlarýnýzý Kontrol Ediniz",
					Toast.LENGTH_LONG).show();
		}

		catch (Exception e) {
			Toast.makeText(Search_AV.this, "Lütfen Baðlantý Ayarlarýnýzý Kontrol Ediniz",
					Toast.LENGTH_LONG).show();
		}

	}

	protected void showCurrentLocation() {

		if (location != null) {
			latitude = location.getLatitude();
			langitude = location.getLongitude();
			String message = String.format(
					"Koordinatlar \n boylam: %1$s \n enlem : %2$s",
					langitude, latitude
					);
			Toast.makeText(Search_AV.this, message,
					Toast.LENGTH_LONG).show();

			updateMap(latitude, langitude);
		}

	}

	protected void updateMap(double latitude, double longitude)
	{
		this.latitude = latitude;
		langitude = longitude;
		
		point = new GeoPoint(
				(int) (latitude * 1E6), 
				(int) (longitude * 1E6));

		mapController.animateTo(point);

		//add overlay items
		OverlayItem overlayitem = new OverlayItem(point, "Bulunduðunuz Adres" , "adres");
		itemoverlay.addOverlay(overlayitem);
		mapOverlays.add(itemoverlay);
		// zoom to position
		mapController.setZoom(17);
		//reload map
		mapView.invalidate();
	}

	public void backMenu ()
	{
		Toast toast;
		toast = Toast.makeText(getApplicationContext(), "Lütfen baðlantýnýzý kontrol ediniz.", Toast.LENGTH_SHORT);
		toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
		toast.show();
		Intent myIntent = new Intent(getApplicationContext(), home.class);
		startActivityForResult(myIntent, 0);
		finish();
	}

	protected void defineGUI(){
		//get value
		adres = (EditText) findViewById(R.id.addText);
		//get mapview
		mapView = (MapView) findViewById(R.id.mapview);
		searchButton = (Button) findViewById(R.id.araButton);

	}

	protected void setListeners() {
		searchButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				if(searchAdress())
				{
					getAVsAround();
				}
			}

		});
	}

	public void getAVsAround (){
		point = new GeoPoint(
				(int) (latitude * 1E6), 
				(int) (langitude * 1E6));
		
		for (int i = 0; i < allAV.size(); i ++)
		{
			GeoPoint point2 = new GeoPoint (
					(int) (allAV.get(i).latitude * 1E6), 
					(int) (allAV.get(i).langitude * 1E6));
			
			if(getDistanceInMeters(point, point2) <= 5000){
				OverlayItem overlayitem = new OverlayItem(point2, allAV.get(i).getPost_title() , allAV.get(i).getPost_content());
				itemoverlay2.addOverlay(overlayitem);
				mapOverlays.add(itemoverlay2);
			}
		}
		
		mapView.invalidate();
		
	}

	public float getDistanceInMeters(GeoPoint p1, GeoPoint p2) {
	    double lat1 = ((double)p1.getLatitudeE6()) / 1e6;
	    double lng1 = ((double)p1.getLongitudeE6()) / 1e6;
	    double lat2 = ((double)p2.getLatitudeE6()) / 1e6;
	    double lng2 = ((double)p2.getLongitudeE6()) / 1e6;
	    float [] dist = new float[1];
	    Location.distanceBetween(lat1, lng1, lat2, lng2, dist);
	    return dist[0];
	}

	public boolean searchAdress() {
		if (adres.getText() != null || adres.getText().length() != 0) {
			Geocoder geoCoder = new Geocoder(this, Locale.getDefault());
			try {
				List<Address> addresses = geoCoder.getFromLocationName(adres.getText().toString(),5);
				if(addresses.size() > 0)
				{
					// get closest
					GeoPoint p = new GeoPoint( (int) (addresses.get(0).getLatitude() * 1E6), 
							(int) (addresses.get(0).getLongitude() * 1E6));
					updateMap(addresses.get(0).getLatitude(), addresses.get(0).getLongitude());
					return true;
				}
				else
					return false;

			} catch (IOException e) {
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Adres Bulunamadý", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
				return false;
			}
		}
		else{
			Toast toast;
			toast = Toast.makeText(getApplicationContext(), "Adres Giriniz", Toast.LENGTH_SHORT);
			toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
			toast.show();
			return false;
		}

	}

	@Override
	protected boolean isRouteDisplayed() {
		// TODO Auto-generated method stub
		return false;
	}

	class LoadAllProducts extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(Search_AV.this);
			pDialog.setMessage("Violationlar Getiriliyor...");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}


		/**
		 * getting All products from url
		 * */
		protected String doInBackground(String... args) {
			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			try {
				// getting JSON string from URL
				JSONObject json = jsonParser.makeHttpRequest(url_listAllAV, "GET", params);
				// Check your log cat for JSON reponse
				//Log.d("All Products: ", json.toString());
				// Checking for SUCCESS TAG
				int success = json.getInt("success");
				if (success == 1) {
					// products found
					// Getting Array of Products
					products = json.getJSONArray("avler");

					// looping through All Products
					for (int i = 0; i < products.length(); i++) {
						JSONObject c = products.getJSONObject(i);
						AV av = new AV();
						int parent =  c.getInt("post_parent");
						String postTip = c.getString("post_type");
						String guid  = c.getString("guid");

						if(parent == 0){
							av.setID(c.getInt("ID"));
							av.setPost_date(c.getString("post_date"));
							av.setPost_content(c.getString("post_content"));
							av.setPost_title(c.getString("post_title"));
							av.setPost_author(c.getInt("post_author"));
							av.setPost_type(postTip);
							av.setPost_parent(parent);
							av.setGuid("");
							allAV.add(av);
						}
						else {
							if(postTip.equals("attachment"))
							{
								for(int k = 0; k < MyAvList.avList.size(); k++) {
									if ((allAV.get(k).ID == parent )){
										allAV.get(k).setGuid(guid);
									}

								}
							}
						}
					}
				} else {
					// no products found
					// Launch Add New product Activity
					backMenu();
				}
			} catch (JSONException e) {
				backMenu();
			}
			catch (Exception e) {
				backMenu();
			}


			return null;
		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			new getPosition().execute();
			pDialog.dismiss();
		}

	}

	class getPosition extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
		}

		/**
		 * getting All products from url
		 * */
		protected String doInBackground(String... args) {
			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			try {
				// getting JSON string from URL
				JSONObject json = jsonParser.makeHttpRequest(url_Pos, "GET", params);
				int success = json.getInt("success");

				if (success == 1) {

					products = json.getJSONArray("poslar");
					// looping through All Products
					for (int i = 0; i < products.length(); i++) {
						JSONObject c = products.getJSONObject(i);
						int idPost = c.getInt("post_id");
						String cat =  c.getString("category");
						double lat = c.getDouble("lat");
						double lang  = c.getDouble("lng");
						AV.getSetAV(idPost, cat, lat, lang);
					}

				} else {
					// do nothing
					backMenu();
				}
			} catch (JSONException e) {
				backMenu();
			}

			return null;
		}
		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			getAVsAround();
		}

	}

}
