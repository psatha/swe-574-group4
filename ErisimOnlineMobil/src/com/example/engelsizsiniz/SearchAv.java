package com.example.engelsizsiniz;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import overlay.ItemOverlay;

import com.example.engelsizsiniz.newViolation.LoadAVTypes;
import android.location.LocationListener;

import com.google.android.maps.GeoPoint;
import com.google.android.maps.MapActivity;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.Overlay;
import com.google.android.maps.OverlayItem;

import android.app.ListActivity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.drawable.Drawable;
import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
public class SearchAv extends MapActivity{
	private static final long MINIMUM_DISTANCE_CHANGE_FOR_UPDATES = 100; // in Meters
	private static final long MINIMUM_TIME_BETWEEN_UPDATES = 60000; // in Milliseconds
	public static double latitude, longitude;

	protected LocationManager locationManager;
	protected Location location;
	protected GeoPoint point;
	protected Button retrieveLocationButton;
	protected MapView mapView;
	protected MapController mapController;
	protected ItemOverlay itemoverlay;
	protected List<Overlay> mapOverlays;
	public static String streetName ="", districtName ="", cityName = "", countryName = "", postCode = "", mahalle = "";
	protected TextView path, adres;
	public static String idDB;
	protected Button backMenu;
	@Override
	public void onCreate(Bundle savedInstanceState) {
		//AssetManager mngr = getApplicationContext().getAssets();
		//otm = new otomatikAdres(mngr);
		super.onCreate(savedInstanceState);
		
			idDB = getIntent().getExtras().getString("id");
			
			
		
		setContentView(R.layout.activity_search_violation);
		defineGUI();
		setListeners();
		setMap();
		showCurrentLocation();
	}
	protected void showCurrentLocation() {

		if (location != null) {
			latitude = location.getLatitude();
			longitude = location.getLongitude();
			String message = String.format(
					"Koordinatlar \n boylam: %1$s \n enlem : %2$s",
					longitude, latitude
					);
			Toast.makeText(SearchAv.this, message,
					Toast.LENGTH_LONG).show();

			updateMap(latitude, longitude);
		}

	}
	private class MyLocationListener implements LocationListener {
				public void onLocationChanged(Location location) {
			/*String message = String.format(
					"Yeni koordinat \n Boylam: %1$s \n Enlem: %2$s",
					location.getLongitude(), location.getLatitude()
					);*/
			//updateMap(location.getLatitude(), location.getLongitude());
		}

		public void onStatusChanged(String s, int i, Bundle b) {
		}

		public void onProviderDisabled(String s) {
		}

		public void onProviderEnabled(String s) {
		}

	}

	@Override
	protected boolean isRouteDisplayed() {
		// TODO Auto-generated method stub
		return false;
	}

	protected void updateMap(double latitude, double longitude)
	{
		point = new GeoPoint(
				(int) (latitude * 1E6), 
				(int) (longitude * 1E6));

		mapController.animateTo(point);
		//add overlay items
		String location = getLocation();
		if(location != ""){
			OverlayItem overlayitem = new OverlayItem(point, "Bulunduðunuz Adres" , getLocation());
			itemoverlay.addOverlay(overlayitem);
			mapOverlays.add(itemoverlay);
		}
		// zoom to position
		mapController.setZoom(17);
		//reload map
		mapView.invalidate();
	}

	protected String getLocation() {

		try {
			//forDongusuAdress();
			Geocoder geocoder = new Geocoder(this, Locale.getDefault());
			Address add = geocoder.getFromLocation(location.getLatitude(), location.getLongitude(), 1).get(0);
			int value = add.getMaxAddressLineIndex();
			StringBuffer str = new StringBuffer();
			
			for (int i = 0; i < value; i ++) {
				str.append(add.getAddressLine(i));
				str.append(" ");
			}
			
			if (value > 1)
				streetName = add.getAddressLine(0) + add.getAddressLine(1);
			else
				streetName = add.getAddressLine(0);
			
			adres.setText(str.toString());

			if (add.getCountryName() != null)
				countryName = add.getCountryName();
			if (add.getPostalCode() != null)
				postCode = add.getPostalCode();
			if (add.getSubLocality() != null)
				mahalle = add.getSubLocality();
			if (add.getLocality() != null)
				districtName = add.getLocality();
			
			
			String val = add.getAddressLine(value - 1);
			if (val.contains("/")) {
				String[] strings = val.split("/");
				cityName = strings[strings.length - 1];
			}
			
			String result = str.toString();
			return result;
		} catch (IOException e) {
			Toast.makeText(SearchAv.this, "Adres Bilgisi Alýnamadý",
					Toast.LENGTH_LONG).show();
			// closing this screen
			backMenu();
			return null;
		}
		
		catch (Exception e) {
			Toast.makeText(SearchAv.this, "Adres Bilgisi Alýnamadý",
					Toast.LENGTH_LONG).show();
			// closing this screen
			backMenu();
			return null;
		}
	}
	public void backMenu ()
	{
		Toast toast;
		toast = Toast.makeText(getApplicationContext(), "Eklenemedi. Lütfen baðlantýnýzý kontrol ediniz.", Toast.LENGTH_SHORT);
		toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
		toast.show();
		Intent myIntent = new Intent(getApplicationContext(), home.class);
		startActivityForResult(myIntent, 0);
		finish();
	}
	protected void defineGUI(){
		
		path = (TextView) findViewById(R.id.filePath);
		adres = (TextView) findViewById(R.id.bulunduguAdres);
		backMenu = (Button) findViewById(R.id.mainmenu);


		
		
	}
	protected void setListeners() {
		backMenu.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), home.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});
}
	protected void setMap ()
	{

		try {
			LocationListener mylocationListener = new MyLocationListener();

			locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);

			//get first location
			Criteria criteria = new Criteria();
			String provider = locationManager.getBestProvider(criteria, true);
			System.out.println(provider);
			//locationManager.requestSingleUpdate(provider, mylocationListener);
			locationManager.requestLocationUpdates(
					provider,
					MINIMUM_TIME_BETWEEN_UPDATES,
					MINIMUM_DISTANCE_CHANGE_FOR_UPDATES,
					mylocationListener
					);

			location = locationManager.getLastKnownLocation(provider);
			//float val = location.getAccuracy();
			//System.out.println("afdafa " + val );


			mapView = (MapView) findViewById(R.id.mapview);
			mapView.setBuiltInZoomControls(true);
			mapController = mapView.getController();

			mapOverlays = mapView.getOverlays();
			Drawable drawable = this.getResources().getDrawable(R.drawable.androidmarker);
			itemoverlay = new ItemOverlay(drawable, this);
		} catch (NotFoundException e) {
			Toast.makeText(SearchAv.this, "Lütfen Baðlantý Ayarlarýnýzý Kontrol Ediniz",
					Toast.LENGTH_LONG).show();
		}

		catch (Exception e) {
			Toast.makeText(SearchAv.this, "Lütfen Baðlantý Ayarlarýnýzý Kontrol Ediniz",
					Toast.LENGTH_LONG).show();
		}

	}	
}