package com.example.engelsizsiniz;

import java.io.BufferedInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.net.URL;
import java.net.URLConnection;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;
import java.util.Locale;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import overlay.ItemOverlay;
import overlay.ItemOverlayForGet;

import com.example.engelsizsiniz.newViolation.LoadAVTypes;
import com.example.engelsizsiniz.newViolation.insertMeta;
import com.google.android.maps.GeoPoint;
import com.google.android.maps.MapActivity;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.Overlay;
import com.google.android.maps.OverlayItem;
import com.jcraft.jsch.Channel;
import com.jcraft.jsch.ChannelSftp;
import com.jcraft.jsch.JSch;
import com.jcraft.jsch.Session;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.drawable.Drawable;
import android.location.Address;
import android.location.Criteria;
import android.location.Geocoder;
import android.location.Location;
import android.location.LocationListener;
import android.location.LocationManager;
import android.net.Uri;
import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

public class Show_AV extends MapActivity {

	static int position;

	//gui values
	protected Spinner disabilityType;
	public static int spinPos = 0;
	protected Button backMenu, updateButton,subscribeButton, unSubscribeButton;
	protected EditText noteText, titleText;
	protected ImageView  imageView;
	protected static TextView adres;
	protected Uri imageUri;

	//map values
	protected LocationManager locationManager;
	protected Location location;
	protected GeoPoint point;
	protected Button retrieveLocationButton;
	protected MapView mapView;
	protected MapController mapController;
	protected static ItemOverlayForGet itemoverlay;
	protected static List<Overlay> mapOverlays;

	public static String note;
	public static String title;
	public static int ID;
	public static String idDB;
	public static String adresInfo;

	private static String url_Pos = "http://swe.cmpe.boun.edu.tr/fall2012g4/getPos.php";
	private static String url_subscribeNewViolation = "http://swe.cmpe.boun.edu.tr/fall2012g4/subscribeNewViolation.php";
	private static String url_querySubscription = "http://swe.cmpe.boun.edu.tr/fall2012g4/querySubscription.php";
	private static String url_unSubscribeViolation = "http://swe.cmpe.boun.edu.tr/fall2012g4/unsubscribeViolation.php";
	private int subscriptionId ;
	JSONParser jsonParser = new JSONParser();
	public ProgressDialog pDialog;
	public JSONArray products = null;

	static double langitude, latitude;
	static String category;
	static String guid;
	
	public static boolean subscribed = false;

	public static File f;
	public ArrayList<String> subsList = new ArrayList();
	JSONArray subscriptions = null;
	private static final String TAG_SUCCESS = "success";
	private static final String TAG_SUBSCRIPTIONS = "subscription";
	
	@Override
	protected void onCreate(Bundle savedInstanceState) {
		// get array position
		super.onCreate(savedInstanceState);
		products = null;
		position = getIntent().getExtras().getInt("position");
		if(getIntent().getExtras().getString("search") == null) {
		
			if(MyAvList.avList.size()!=0){
				note = MyAvList.avList.get(position).getPost_content();
				title = MyAvList.avList.get(position).getPost_title();
				ID = MyAvList.avList.get(position).getID();
				guid = MyAvList.avList.get(position).getGuid();
				idDB = getIntent().getExtras().getString("userid");
			}
		}
		else {
			if(Search_AV.allAV.size()!=0){
				note = Search_AV.allAV.get(position).getPost_content();
				title = Search_AV.allAV.get(position).getPost_title();
				ID = Search_AV.allAV.get(position).getID();
				guid = Search_AV.allAV.get(position).getGuid();
				idDB = Search_AV.idDb;
			}
		}

		new fileDownload().execute();
		new checkIfSubscribed().execute();
		setContentView(R.layout.activity_show__av);
		defineGUI();
		setListeners();
		new getPosition().execute();
		//new QuerySubscription().execute();

		
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.activity_show__av, menu);
		return true;
	}

	protected void setMap ()
	{
		// map settings
		try {

			mapView.setBuiltInZoomControls(true);
			mapController = mapView.getController();

			mapOverlays = mapView.getOverlays();
			Drawable drawable = this.getResources().getDrawable(R.drawable.androidmarker);
			itemoverlay = new ItemOverlayForGet(drawable,this);

			updateMap(latitude, langitude);

			//getLocation();

			//adres.setText(adresInfo);

		} catch (NotFoundException e) {

		}

		catch (Exception e) {

		}

	}

	protected void updateMap(double latitude, double longitude)
	{
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
		disabilityType = (Spinner) findViewById(R.id.avSpin);
		//get value
		backMenu = (Button) findViewById(R.id.backtolist);
		subscribeButton=(Button)findViewById(R.id.subscribeButton);
		unSubscribeButton=(Button)findViewById(R.id.unsubscribeButton);
		adres = (TextView) findViewById(R.id.AVadres);
		//get adres
		imageView = (ImageView) findViewById(R.id.imageView2);
		//get image
		noteText = (EditText) findViewById(R.id.AVnoteText);
		noteText.setText(note);
		noteText.setFocusable(false);
		noteText.setEnabled(false);
		titleText = (EditText) findViewById(R.id.AVtitleText);
		titleText.setText(title);
		titleText.setFocusable(false);
		titleText.setEnabled(false);
		mapView = (MapView) findViewById(R.id.mapview);


		//city.setEnabled(false);
		//district.setEnabled(false);
		//street.setEnabled(false);
	}

	protected void setListeners() {
		backMenu.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				finish();
			}

		});
		
		subscribeButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				new SubscribeViolation().execute();
			}

		});
		
		unSubscribeButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				// un subs ile degistir
				new UnSubscribeViolation().execute();
				System.out.println("un basýldý");
			}

		});
	}

	public void imageClick (View view) {
		Intent intent = new Intent();
		intent.setAction(Intent.ACTION_VIEW);
		intent.setDataAndType(Uri.parse("file://" + f.getAbsolutePath()), "image/*");
		startActivity(intent);
	}

	protected void getLocation() {

		try {
			//forDongusuAdress();
			Geocoder geocoder = new Geocoder(this, Locale.getDefault());
			Address add = geocoder.getFromLocation(latitude, langitude, 1).get(0);
			int value = add.getMaxAddressLineIndex();
			StringBuffer str = new StringBuffer();

			for (int i = 0; i < value; i ++) {
				str.append(add.getAddressLine(i));
				str.append(" ");
			}

			adresInfo = str.toString();
			//adres.setText(adresInfo);

		} catch (IOException e) {
			Toast.makeText(Show_AV.this, "Adres Bilgisi Alýnamadý",
					Toast.LENGTH_LONG).show();
			// closing this screen
			backMenu();
		}
	}

	public void setSpinner() {
		String[] avListString = new String[1];
		avListString[0] = category;
		ArrayAdapter<String> avAdapter = new ArrayAdapter<String>(Show_AV.this,
				android.R.layout.simple_spinner_item, avListString);
		disabilityType.setAdapter(avAdapter);
		disabilityType.setSelection(0, true);
	}

	@Override
	protected boolean isRouteDisplayed() {
		// TODO Auto-generated method stub
		return false;
	}

	class fileDownload extends AsyncTask<String, String, String> {

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
			picDownload();
			return null;
		}
		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
		}

		public void picDownload() {
			try {
				URL url = new URL(guid);
				URLConnection connection = url.openConnection();
				connection.connect();
				// this will be useful so that you can show a typical 0-100% progress bar
				int fileLength = connection.getContentLength();
				SimpleDateFormat dateFormat = new SimpleDateFormat("yyyyMMdd-HHmmss");
				String targetFileName = "IMG_" + dateFormat.format(new Date()) + ".jpg";
				// download the file
				InputStream input = new BufferedInputStream(url.openStream());
				f = new File(Environment.getExternalStorageDirectory(),targetFileName);
				OutputStream output = new FileOutputStream(f);

				byte data[] = new byte[1024];
				long total = 0;
				int count;
				while ((count = input.read(data)) != -1) {
					total += count;
					// publishing the progress....
					output.write(data, 0, count);
				}

				output.flush();
				output.close();
				input.close();

				Bitmap thumbnail;
				BitmapFactory.Options bitmapOptions = new BitmapFactory.Options();  
				bitmapOptions.inSampleSize = 1;  
				thumbnail = BitmapFactory.decodeFile(f.getAbsolutePath(), bitmapOptions);
				imageView.setImageBitmap(thumbnail);

			} catch (Exception e) {
			}

		}

	}


	class getPosition extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(Show_AV.this);
			pDialog.setMessage("Violation Ýndiriliyor...");
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
				params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));

				JSONObject json = jsonParser.makeHttpRequest(url_Pos, "GET", params);
				// Check your log cat for JSON reponse
				//Log.d("All Products: ", json.toString());
				// Checking for SUCCESS TAG
				int success = json.getInt("success");


				if (success == 1) {

					products = json.getJSONArray("poslar");

					// looping through All Products
					for (int i = 0; i < products.length(); i++) {
						JSONObject c = products.getJSONObject(i);
						category =  c.getString("category");
						latitude = c.getDouble("lat");
						langitude  = c.getDouble("lng");
					}

					new spinnerSet().execute();
					
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
			// dismiss the dialog after getting all products
			// updating UI from Background Thread
			setMap();
			pDialog.dismiss();
			Toast toast;
			toast = Toast.makeText(getApplicationContext(), "Resme Týklayýp Galeride Açabilirsiniz", Toast.LENGTH_SHORT);
			toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
			toast.show();


		}

	}

	class spinnerSet extends AsyncTask<String, String, String> {

		ArrayAdapter<String> avAdapter;

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
			String[] avListString = new String[1];
			avListString[0] = category;
			avAdapter = new ArrayAdapter<String>(Show_AV.this,
					android.R.layout.simple_spinner_item, avListString);
			return null;
		}
		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog after getting all products
			// updating UI from Background Thread
			disabilityType.setAdapter(avAdapter);
			disabilityType.setSelection(0, true);

		}

	}
class SubscribeViolation extends AsyncTask<String, String, String> {

		
		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(Show_AV.this);
			pDialog.setMessage("Violation Takibi Yapýlýyor...");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}

		

		/**
		 * Creating subscribtion
		 * */
		protected String doInBackground(String... args) {

			
			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("user_id", idDB));
			params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
			params.add(new BasicNameValuePair("username", home.username));
			params.add(new BasicNameValuePair("usermail", home.email));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_subscribeNewViolation,
					"GET", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					// successfully created product
						subscribed = true; 
				} else {
					// failed to create product
					backMenu();
				}
			} catch (JSONException e) {
				backMenu();
				e.printStackTrace();
			}

			return null;
		}


		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			
			if(subscribed){
				subscribeButton.setVisibility(View.GONE);
				unSubscribeButton.setVisibility(View.VISIBLE);
			}
			pDialog.dismiss();
		}

		

	}


class UnSubscribeViolation extends AsyncTask<String, String, String> {

	
	/**
	 * Before starting background thread Show Progress Dialog
	 * */
	@Override
	protected void onPreExecute() {
		
		super.onPreExecute();
		pDialog = new ProgressDialog(Show_AV.this);
		pDialog.setMessage("Violation Takibi Býrakýlýyor...");
		pDialog.setIndeterminate(false);
		pDialog.setCancelable(true);
		pDialog.show();
		
	}

	

	/**
	 * Creating subscribtion
	 * */
	protected String doInBackground(String... args) {

		
		// Building Parameters
		List<NameValuePair> params = new ArrayList<NameValuePair>();
		params.add(new BasicNameValuePair("user_id", idDB));
		params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
		

		// getting JSON Object
		// Note that create product url accepts POST method
		JSONObject json = jsonParser.makeHttpRequest(url_unSubscribeViolation,
				"GET", params);

		// check log cat fro response
		Log.d("Create Response", json.toString());

		// check for success tag
		try {
			int success = json.getInt(TAG_SUCCESS);

			if (success == 1) {
				// successfully created product
					subscribed = false; 
			} else {
				// failed to create product
				backMenu();
			}
		} catch (JSONException e) {
			backMenu();
			e.printStackTrace();
		}

		return null;
	}


	/**
	 * After completing background task Dismiss the progress dialog
	 * **/
	protected void onPostExecute(String file_url) {
		// dismiss the dialog once done
		if(!subscribed){
			subscribeButton.setVisibility(View.VISIBLE);
			unSubscribeButton.setVisibility(View.GONE);
		}
		pDialog.dismiss();
	}

	

}





class checkIfSubscribed extends AsyncTask<String, String, String> {

	
	/**
	 * Before starting background thread Show Progress Dialog
	 * */
	@Override
	protected void onPreExecute() {
		
	}


	/**
	 * Creating subscribtion
	 * */
	protected String doInBackground(String... args) {
		
		// Building Parameters
		List<NameValuePair> params = new ArrayList<NameValuePair>();
		params.add(new BasicNameValuePair("user_id", idDB));
		params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
		
		
		// getting JSON Object
		// Note that create product url accepts POST method
		JSONObject json = jsonParser.makeHttpRequest(url_querySubscription,
				"GET", params);

		// check log cat fro response
		Log.d("JSON CEVABI GELIYOR", json.toString());
		System.out.println("JSONDA  GELDI");
		// check for success tag
		try {
			int success = json.getInt(TAG_SUCCESS);

			if (success == 1) {
				// user already subscribed
				subscribed = true;
		
			} else {
				subscribed = false;
				// usernot subscribed
			}
		} catch (JSONException e) {
			backMenu();
			e.printStackTrace();
		}
		
		
		return null;
	}


	/**
	 * After completing background task Dismiss the progress dialog
	 * **/
	protected void onPostExecute(String file_url) {
		// dismiss the dialog once done
		if(subscribed){
			subscribeButton.setVisibility(View.GONE);
			unSubscribeButton.setVisibility(View.VISIBLE);
		}
		else {
			subscribeButton.setVisibility(View.VISIBLE);
			unSubscribeButton.setVisibility(View.GONE);
		}
	}

	

}

}
