package com.example.engelsizsiniz;

import java.io.File;
import java.io.IOException;
import java.io.InputStream;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import overlay.ItemOverlay;

import com.example.adress.otomatikAdres;
import com.google.android.maps.GeoPoint;
import com.google.android.maps.MapActivity;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.Overlay;
import com.google.android.maps.OverlayItem;


import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.MailTo;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.provider.MediaStore;
import android.app.Application;
import android.content.Context;
import android.content.Intent;
import android.content.res.AssetManager;
import android.database.Cursor;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.drawable.Drawable;
import android.view.Menu;
import android.view.MotionEvent;
import android.view.View;
import android.widget.AnalogClock;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.ImageView;
import android.widget.RadioButton;
import android.widget.RadioGroup;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

public class newViolation extends MapActivity {

	private static final long MINIMUM_DISTANCE_CHANGE_FOR_UPDATES = 10; // in Meters
	private static final long MINIMUM_TIME_BETWEEN_UPDATES = 60000; // in Milliseconds
	private double latitude, longitude;

	protected LocationManager locationManager;
	protected Location location;
	protected GeoPoint point;
	protected Button retrieveLocationButton;
	protected MapView mapView;
	protected MapController mapController;
	protected ItemOverlay itemoverlay;
	protected List<Overlay> mapOverlays;

	//protected Spinner disabilityType, city, district, street;
	protected Spinner disabilityType;
	protected Button backMenu, uploadButton, photoButton;
	protected TextView path, adres;
	protected Uri imageUri;
	
	public otomatikAdres otm;
	protected File photo;

	String streetName ="", districtName ="", cityName = "";

	@Override
	public void onCreate(Bundle savedInstanceState) {
		AssetManager mngr = getApplicationContext().getAssets();
		otm = new otomatikAdres(mngr);
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_new_violation);
		defineGUI();
		setListeners();
		setMap();
		showCurrentLocation();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_new_violation, menu);
		return true;
	}

	protected void setListeners() {
		backMenu.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), home.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});

		uploadButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent i = new Intent(Intent.ACTION_PICK, android.provider.MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
				startActivityForResult(i, 1);
			}

		});
		
		
		photoButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent i = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
				SimpleDateFormat dateFormat = new SimpleDateFormat("yyyyMMdd-HHmmss");
				String fileName = "IMG_" + dateFormat.format(new Date()) + ".jpg";
				photo = new File(Environment.getExternalStoragePublicDirectory(
				        Environment.DIRECTORY_PICTURES),fileName);
				i.putExtra(MediaStore.EXTRA_OUTPUT, Uri.fromFile(photo));
				startActivityForResult(i, 0);
			}

		});
		
		/*
		radioGroup.setOnCheckedChangeListener(new RadioGroup.OnCheckedChangeListener() {
		      public void onCheckedChanged(RadioGroup arg0, int id) {
		        switch (id) {
		        case R.id.currentLocation:
		        	setSpinners(false);
		          break;
		        case R.id.otherLocation:
		        	setSpinners(true);
		          break;
		        default:
		          break;
		        }
		      }
		    });
		    */
	}

	
	
	public void setSpinners(boolean status)
	{
		/*
		if(status) {
			city.setEnabled(true);
			district.setEnabled(true);
			street.setEnabled(true);
		}
		else {
			city.setEnabled(false);
			district.setEnabled(false);
			street.setEnabled(false);
		}
		*/
			
	}

	@Override
	protected void onActivityResult(int requestCode, int resultCode, Intent data) {
		super.onActivityResult(requestCode, resultCode, data);
		InputStream inputStream = null;
		
		if (requestCode == 1 && resultCode == RESULT_OK && null != data) {
			Uri selectedImage = data.getData();
			String[] filePathColumn = { MediaStore.Images.Media.DATA };

			Cursor cursor = getContentResolver().query(selectedImage,
					filePathColumn, null, null, null);
			cursor.moveToFirst();

			int columnIndex = cursor.getColumnIndex(filePathColumn[0]);
			String picturePath = cursor.getString(columnIndex);
			cursor.close();
			String [] pictureArray = picturePath.toString().split("/");
			path.setText(pictureArray[pictureArray.length-1]);
			// imageView.setImageBitmap(BitmapFactory.decodeFile(picturePath));

		}
		
		else if (requestCode == 0 && resultCode == RESULT_OK) {
			
			path.setText(photo.getPath().toString());
			// imageView.setImageBitmap(BitmapFactory.decodeFile(picturePath));

		}
	}

	protected void setMap ()
	{

		LocationListener mylocationListener = new MyLocationListener();

		locationManager = (LocationManager) getSystemService(Context.LOCATION_SERVICE);

		//get first location
		Criteria criteria = new Criteria();
		String provider = locationManager.getBestProvider(criteria, true);
		
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
		
	}

	
	
	protected void showCurrentLocation() {

		if (location != null) {
			latitude = location.getLatitude();
			longitude = location.getLongitude();
			String message = String.format(
					"Koordinatlar \n boylam: %1$s \n enlem : %2$s",
					longitude, latitude
					);
			Toast.makeText(newViolation.this, message,
					Toast.LENGTH_LONG).show();
		}
		
		updateMap(latitude, longitude);
	}

	private class MyLocationListener implements LocationListener {

		public void onLocationChanged(Location location) {
			String message = String.format(
					"Yeni koordinat \n Boylam: %1$s \n Enlem: %2$s",
					location.getLongitude(), location.getLatitude()
					);
			updateMap(location.getLatitude(), location.getLongitude());
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

		Geocoder geocoder = new Geocoder(this, Locale.getDefault());
		try {
			//forDongusuAdress();

			Address add = geocoder.getFromLocation(location.getLatitude(), location.getLongitude(), 1).get(0);
			if(add == null)
				return "";

			//Toast.makeText(newViolation.this, add.getAddressLine(0) + " " + add.getAddressLine(1) + " " + add.getAddressLine(2) + " " + add.getAddressLine(3),
			//Toast.LENGTH_LONG).show();
			/*
			if(add.getAddressLine(2) != null) {
				String[] splitted = add.getAddressLine(2).split(" ");
				cityName = splitted[1];
			}
			if(add.getAddressLine(0) != null) {
				districtName = add.getAddressLine(0); 
			}
			if(add.getAddressLine(1) != null) {
				streetName = add.getAddressLine(1);
			}*/
			adres.setText(add.getAddressLine(0) + " " + add.getAddressLine(1) + " " + add.getAddressLine(2));

			String result = districtName + " -- " + streetName + " -- " + cityName + "  --";
			return result;
		} catch (IOException e) {
			return null;
		}
	}

	public void forDongusuAdress()
	{
		Geocoder geocoder = new Geocoder(this, Locale.getDefault());
		List<Address> addList = null;
		try {
			addList = geocoder.getFromLocation(location.getLatitude(), location.getLongitude(), 10);
		} catch (IOException e1) {
			e1.printStackTrace();
		}
		for(int i = 0; i < 10; i ++) {
			Address add = addList.get(i);
			String result = add.getAddressLine(0) + " -- " + add.getAddressLine(1) + " -- " + add.getAddressLine(2) + "  --" + add.getAddressLine(3);
			System.out.println(result);
		}
	}

	public void uploadFile(){

	}

	protected void defineGUI(){
		disabilityType = (Spinner) findViewById(R.id.disabilitySpin);
		//city = (Spinner) findViewById(R.id.citySpin);
		//district = (Spinner) findViewById(R.id.districtSpin);
		//street = (Spinner) findViewById(R.id.streetSpin);
		backMenu = (Button) findViewById(R.id.mainmenu);
		uploadButton = (Button) findViewById(R.id.uploadButton);
		photoButton = (Button) findViewById(R.id.photoButton);
		path = (TextView) findViewById(R.id.filePath);
		adres = (TextView) findViewById(R.id.bulunduguAdres);
		//city.setEnabled(false);
		//district.setEnabled(false);
		//street.setEnabled(false);
	}

	protected void setSpinnerAdapters()
	{
		/*
		//city value
		String[] city_spinner=new String[otm.iller.size()];
		//Toast.makeText(newViolation.this, otm.iller.get(1).getIlAdi() ,Toast.LENGTH_LONG).show();
		for (int i = 0 ; i < otm.iller.size(); i ++)
		{
			city_spinner[i] = otm.iller.get(i).getIlAdi();
		}
		ArrayAdapter<String> cityadapter = new ArrayAdapter<String>(this,
				android.R.layout.simple_spinner_item, city_spinner);
		city.setAdapter(cityadapter);

		//district value
		String[] dist_spinner=new String[1];
		dist_spinner[0] = districtName;
		ArrayAdapter<String> distadapter = new ArrayAdapter<String>(this,
				android.R.layout.simple_spinner_item, dist_spinner);
		district.setAdapter(distadapter);

		//street value
		String[] street_spinner = new String[1];
		street_spinner[0] = streetName;
		ArrayAdapter<String> streetadapter = new ArrayAdapter<String>(this,
				android.R.layout.simple_spinner_item, street_spinner);
		street.setAdapter(streetadapter);
		*/
	}

}



