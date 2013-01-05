package com.example.engelsizsiniz;

import java.io.BufferedInputStream;
import java.io.ByteArrayOutputStream;
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
import com.example.engelsizsiniz.newViolation.insertCategory;
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
import android.provider.MediaStore;
import android.app.Activity;
import android.app.AlertDialog;
import android.app.ProgressDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.content.res.Resources.NotFoundException;
import android.database.Cursor;
import android.text.SpannableString;
import android.text.style.UnderlineSpan;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.CompoundButton;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;

public class Show_AV extends MapActivity {

	static int position;

	//gui values
	protected Spinner disabilityType;
	public static int spinPos = 0;
	protected Button updateAVButton, updateButton;
	protected ImageButton subscribeButton, unSubscribeButton;
	private CheckBox solvedBox;
	public static String solved = "no";
	protected EditText noteText, titleText;
	protected ImageView  imageView;
	protected static TextView adres;
	protected Uri imageUri;
	protected TextView commentText;
	protected TextView textView02,textView03,textView04;
	protected Spinner  avValSpin,avValSpin03,avValSpin04;
	protected EditText  avValText,avValText03,avValText04;
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

	protected static File photo;

	protected Button backMenu, uploadButton, photoButton, submitButton;
	protected TextView path;


	public static String fileName;
	public static String filePathIntent ="", titleIntent="", noteIntent="";
	public static String noteDB, titleDB, spinnerDB, filePathDB;
	public static ArrayList<String> fileDeleted = new ArrayList<String>();

	private static String url_Pos = "http://swe.cmpe.boun.edu.tr/fall2012g4/getPos.php";
	private static String url_updateViolation = "http://swe.cmpe.boun.edu.tr/fall2012g4/updateViolation.php";
	private static String url_AVSolved = "http://swe.cmpe.boun.edu.tr/fall2012g4/AVSolved.php";
	private static String url_AddMeta = "http://swe.cmpe.boun.edu.tr/fall2012g4/newTermMeta.php";
	private static String url_subscribeNewViolation = "http://swe.cmpe.boun.edu.tr/fall2012g4/subscribeNewViolation.php";
	private static String url_querySubscription = "http://swe.cmpe.boun.edu.tr/fall2012g4/querySubscription.php";
	private static String url_unSubscribeViolation = "http://swe.cmpe.boun.edu.tr/fall2012g4/unsubscribeViolation.php";
	private static String url_metaUpdate = "http://swe.cmpe.boun.edu.tr/fall2012g4/metaUpdate.php";
	private static String url_av_fields = "http://swe.cmpe.boun.edu.tr/fall2012g4/avFieldsToShowAv.php";
	private static String url_getAvMeta = "http://swe.cmpe.boun.edu.tr/fall2012g4/getAvMeta.php";


	private int subscriptionId ;
	JSONParser jsonParser = new JSONParser();
	public ProgressDialog pDialog;
	public JSONArray products = null;
    JSONArray categoryFields =null;
    JSONArray metaList =null;


	static double langitude, latitude;
	static String category;
	static String guid;

	public static boolean subscribed = false;

	public static boolean exist = true;

	public static File f;
	public ArrayList<String> subsList = new ArrayList();
	JSONArray subscriptions = null;
	private static final String TAG_SUCCESS = "success";
	private static final String TAG_SUBSCRIPTIONS = "subscription";
	public static ArrayList<Field> fields = new ArrayList<Field>();
	public static ArrayList<Meta> metas = new ArrayList<Meta>();


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
		new checkIfSolved().execute();
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
		updateAVButton = (Button) findViewById(R.id.updateAV);
		solvedBox = (CheckBox) findViewById(R.id.solvedBox);
		subscribeButton=(ImageButton)findViewById(R.id.subscribeButton);
		unSubscribeButton=(ImageButton)findViewById(R.id.unsubscribeButton);
		textView02 =(TextView)findViewById(R.id.TextView02);
		textView03 =(TextView)findViewById(R.id.TextView03);
		textView04 =(TextView)findViewById(R.id.TextView04);
		/*textView05 =(TextView)findViewById(R.id.TextView05);
		textView06 =(TextView)findViewById(R.id.TextView06);*/
		avValSpin = (Spinner) findViewById(R.id.AVValspinner);
		avValText = (EditText) findViewById(R.id.AVValeditText);

		avValSpin03 = (Spinner) findViewById(R.id.AVValspinner03);
		avValText03 = (EditText) findViewById(R.id.AVValeditText03);
		avValSpin04 = (Spinner) findViewById(R.id.AVValspinner04);
		avValText04 = (EditText) findViewById(R.id.AVValeditText04);
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
		commentText = (TextView) findViewById(R.id.CommentText);
		
		String data = "Yorum Yaz ve Oku";
		SpannableString str = new SpannableString(data);
		str.setSpan(new UnderlineSpan(), 0, data.length(), 0);
		commentText.setText(str);
		
		//city.setEnabled(false);
		//district.setEnabled(false);
		//street.setEnabled(false);
	}

	protected void setListeners() {
		
		updateAVButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				dialogBoxGenerate();
			}

		}); 

		subscribeButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				new SubscribeViolation().execute();
			}

		});

		solvedBox.setOnCheckedChangeListener(new CompoundButton.OnCheckedChangeListener(){

			public void onCheckedChanged(CompoundButton buttonView, boolean isChecked) {
				// TODO Auto-generated method stub
				if(isChecked) {
					solved = "yes";
				}
				else {
					solved = "no";
				}

			}
		});

		unSubscribeButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				// un subs ile degistir
				new UnSubscribeViolation().execute();
				System.out.println("un basýldý");
			}

		});
		
		commentText.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				//texte basýldý if not 0
				Intent myIntent = new Intent(getApplicationContext(), ShowComment.class);
				myIntent.putExtra("id", ID);
				startActivityForResult(myIntent, 0);
				finish();
			}
		});
	}

	public void dialogBoxGenerate () {
		AlertDialog.Builder builder = new AlertDialog.Builder(Show_AV.this);
		builder.setMessage("Resim yukle")
		.setCancelable(true)
		.setPositiveButton("Resim Cek", new DialogInterface.OnClickListener() {
			public void onClick(DialogInterface dialog, int id) {
				//resim çek
				SimpleDateFormat dateFormat = new SimpleDateFormat("yyyyMMdd-HHmmss");
				fileName = "IMG_" + dateFormat.format(new Date()) + ".jpg";
				fileName = fileName.replaceAll("-", "_");
				photo = new File(Environment.getExternalStorageDirectory(),fileName);
				filePathIntent = photo.getAbsolutePath();
				filePathDB = filePathIntent;
				Intent i = new Intent(MediaStore.ACTION_IMAGE_CAPTURE);
				i.putExtra(MediaStore.EXTRA_OUTPUT, Uri.fromFile(photo));
				startActivityForResult(i, 2);
			}
		})
		.setNegativeButton("Galeriden Sec", new DialogInterface.OnClickListener() {
			public void onClick(DialogInterface dialog, int id) {
				//galeriden
				Intent i = new Intent(Intent.ACTION_PICK, android.provider.MediaStore.Images.Media.EXTERNAL_CONTENT_URI);
				startActivityForResult(i, 1);
			}
		});
		AlertDialog alert = builder.create();
		alert.show();
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
			filePathDB = picturePath;
			cursor.close();
			String [] pictureArray = picturePath.toString().split("/");
			//path.setText(selectedImage.getPath());
			Bitmap thumbnail;
			BitmapFactory.Options bitmapOptions = new BitmapFactory.Options();  
			bitmapOptions.inSampleSize = 4;  
			thumbnail = BitmapFactory.decodeFile(picturePath, bitmapOptions);
			imageView.setImageBitmap(thumbnail);

		}

		else if (requestCode == 2 && resultCode == RESULT_OK) {

			if(data != null)
			{
				Bitmap bm = (Bitmap) data.getExtras().get("data");
				bm = Bitmap.createScaledBitmap(bm,100, 100,true);
				imageView.setImageBitmap(bm); // Display image in the View
			}
		}
		new updateViolation().execute();
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
			new GetAVMeta().execute();
			new GetAVFields().execute();

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


	class checkIfSolved extends AsyncTask<String, String, String> {


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
			params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
			params.add(new BasicNameValuePair("meta_key", "cp_av_solved"));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_AVSolved,
					"GET", params);

			// check log cat fro response
			Log.d("JSON CEVABI GELIYOR", json.toString());
			System.out.println("JSONDA  GELDI");
			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					products = json.getJSONArray("meta");

					// looping through All Products
					for (int i = 0; i < products.length(); i++) {
						JSONObject c = products.getJSONObject(i);
						solved = c.getString("meta_value");
						exist = true;
					}

				} else {
					exist = false;
					solved = "no";
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
			if(solved.equals("no")){
				solvedBox.setChecked(false);
			}
			else {
				solvedBox.setChecked(true);
			}
		}

	}

	class updateViolation extends AsyncTask<String, String, String> {

		JSch jsch;
		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(Show_AV.this);
			pDialog.setMessage("Violation Güncelleniyor...");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}

		public String convertPostName (String s){

			s = s.toLowerCase();
			s = s.replaceAll(" ", "-");
			s = s.replace("ü", "u");
			s = s.replace("ý", "i");
			s = s.replace("ö", "o");
			s = s.replace("þ", "s");
			s = s.replace("ð", "g");
			s = s.replace("ç", "c");
			s = s.replace("ð", "g");
			return s;
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {

			if (submitForAttachment())
			{

			}
			else{
				backMenu();
			}
			return null;
		}

		public void uploadFile(){

			String SFTPHOST = "swe.cmpe.boun.edu.tr";
			int    SFTPPORT = 22;
			String SFTPUSER = "fall2012g4";
			String SFTPPASS = "axc25";
			String SFTPWORKINGDIR = "/home/fall2012g4/www/wp-content/uploads/2012/11/";
			Session     session     = null;
			Channel     channel     = null;
			ChannelSftp channelSftp = null;
			try{
				jsch = new JSch();
				session = jsch.getSession(SFTPUSER,SFTPHOST,SFTPPORT);
				session.setPassword(SFTPPASS);
				java.util.Properties config = new java.util.Properties();
				config.put("StrictHostKeyChecking", "no");
				config.put("compression.s2c", "zlib,none");
				config.put("compression.c2s", "zlib,none");
				session.setConfig(config);
				session.connect();
				channel = session.openChannel("sftp");
				channel.connect();
				channelSftp = (ChannelSftp)channel;
				channelSftp.cd(SFTPWORKINGDIR);
				//File f = new File(filePathDB);
				File f = createResizedCopy(210, 210, true);
				channelSftp.put(new FileInputStream(f), f.getName());
				//f.delete();
				f = createResizedCopy(75, 75, false);
				channelSftp.put(new FileInputStream(f), f.getName());

				channelSftp.disconnect();
				session.disconnect();
				//f.delete();
			}catch(Exception ex){
				//backMenu();
				ex.printStackTrace();
			}
		}

		public File createResizedCopy(int scaledWidth, int scaledHeight, boolean original)
		{
			Bitmap scaledphoto = null;
			ByteArrayOutputStream bytes = new ByteArrayOutputStream();
			int height = scaledHeight;
			int width = scaledWidth;    

			Bitmap photo = BitmapFactory.decodeFile( filePathDB );
			scaledphoto = Bitmap.createScaledBitmap(photo, height, width, true);

			try {
				if (!original){
					scaledphoto.compress(Bitmap.CompressFormat.JPEG, 50, bytes);
					String[] names = filePathDB.split("/");
					String fileNameValue = names[names.length-1];
					String[] onlyname = fileNameValue.split("\\.");
					fileNameValue = onlyname[0];
					fileNameValue = fileNameValue.replaceAll("-", "_");
					File f = new File(Environment.getExternalStorageDirectory(),fileNameValue+"-"+scaledWidth+"x"+scaledHeight+".jpg");
					f.createNewFile();
					fileDeleted.add(f.getAbsolutePath());
					FileOutputStream fo = new FileOutputStream(f);
					fo.write(bytes.toByteArray());
					fo.close();
					return f;
				}
				else {
					scaledphoto.compress(Bitmap.CompressFormat.JPEG, 80, bytes);
					String[] names = filePathDB.split("/");
					String fileNameValue = names[names.length-1];
					fileNameValue = fileNameValue.replaceAll("-", "_");
					File f = new File(Environment.getExternalStorageDirectory(),fileNameValue);
					f.createNewFile();
					FileOutputStream fo = new FileOutputStream(f);
					fo.write(bytes.toByteArray());
					fo.close();
					return f;
				}
			} catch (IOException e) {
				// TODO Auto-generated catch block
				backMenu();
				return null;
			}
		}


		public boolean submitForAttachment(){
			Date date = new Date( );
			SimpleDateFormat ft = new SimpleDateFormat ("yyyy-MM-dd kk:mm:ss");
			String time = ft.format(date).toString();
			String[] names = filePathDB.split("/");
			String fileNameValue = names[names.length-1];
			fileNameValue = fileNameValue.replaceAll("-", "_");
			uploadFile();
			// Building Parameters

			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("post_author", idDB));
			params.add(new BasicNameValuePair("post_date", time));
			params.add(new BasicNameValuePair("post_date_gmt", time));
			params.add(new BasicNameValuePair("post_content", ""));
			params.add(new BasicNameValuePair("post_title", titleDB));
			params.add(new BasicNameValuePair("post_status", "inherit"));
			params.add(new BasicNameValuePair("comment_status", "open"));
			params.add(new BasicNameValuePair("ping_status", "open"));
			params.add(new BasicNameValuePair("post_name", ""));
			params.add(new BasicNameValuePair("post_parent", Integer.toString(ID)));
			params.add(new BasicNameValuePair("guid", "http://swe.cmpe.boun.edu.tr/fall2012g4/wp-content/uploads/2012/11/" + fileNameValue));
			params.add(new BasicNameValuePair("menu_order", "0"));
			params.add(new BasicNameValuePair("post_type", "attachment"));
			params.add(new BasicNameValuePair("post_mime_type", "image/jpeg"));
			params.add(new BasicNameValuePair("comment_count", "0"));
			//params.add(new BasicNameValuePair("display_name", usernameDB));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_updateViolation,
					"GET", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			int success;
			try {
				success = json.getInt(TAG_SUCCESS);
				if (success == 1){
					updateMeta();
					return true;
				}
				else{
					backMenu();
					return false;
				}
			} catch (JSONException e) {
				backMenu();
				return false;
			}
		}

		public void updateMeta () {
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
			params.add(new BasicNameValuePair("meta_key", "cp_av_solved"));
			params.add(new BasicNameValuePair("meta_value", solved));
			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json;
			if (!exist) {
			json = jsonParser.makeHttpRequest(url_AddMeta,
					"GET", params);
			}else {
			json = jsonParser.makeHttpRequest(url_metaUpdate,
						"GET", params);
			}
			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {

				} else {
				}
			} catch (JSONException e) {
				backMenu();
				e.printStackTrace();
			}


		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			pDialog.dismiss();
			Toast toast;
			toast = Toast.makeText(getApplicationContext(), "Violation Güncellendi", Toast.LENGTH_SHORT);
			toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
			toast.show();
			deleteFiles();

			Intent myIntent = new Intent(getApplicationContext(), home.class);
			startActivityForResult(myIntent, 0);
			finish();
		}

		public void deleteFiles()
		{
			File file = null;
			for(int i = 0; i < fileDeleted.size(); i ++){
				file = new File(fileDeleted.get(i));
				file.delete();
			}
		}

	}
	class GetAVFields extends AsyncTask<String, String, String> {

		
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
			try {
				// getting JSON string from URL
				String categoryName =convertAddName(category);
				params.add(new BasicNameValuePair("category_name", category));
				
				
				JSONObject json = jsonParser.makeHttpRequest(url_av_fields, "GET", params);
				// Check your log cat for JSON reponse
				//Log.d("All Products: ", json.toString());
				// Checking for SUCCESS TAG
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					
					categoryFields = json.getJSONArray("fields");

					// looping through All Products
					for (int i = 0; i < categoryFields.length(); i++) {
						JSONObject c = categoryFields.getJSONObject(i);

						// Storing each json item in variable
					
					//	int fieldId =Integer.parseInt( c.getString("field_Id"));
						String fieldName = c.getString("field_name");
						String fieldLabel = c.getString("field_label");
						String fieldType = c.getString("field_type");
						String fieldValues = c.getString("field_values");
						String fieldTooltip = c.getString("field_tooltip");
					//	boolean fieldReq = (c.getString("field_req").isEmpty()||c.getString("field_req").equalsIgnoreCase("0"))? false :true;
					//	int fieldMinLength = Integer.parseInt(c.getString("field_min_length"));
					//	int fieldMaxValue = Integer.parseInt(c.getString("field_max_value"));
					//	int fieldMinValue =Integer.parseInt( c.getString("field_min_value"));
						
						fields.add(new Field(fieldName,fieldLabel,fieldType,fieldValues,fieldTooltip));
						
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
			
			// dismiss the dialog once done
			
			createAVInput(fields);
		}
		public void createAVInput(  ArrayList<Field> fields) {

			
			// bu noktada seçili av tipine göre textbox veya spinner gözükecek ve içerisinde valuelar olacak
			/*if (pos != 0) {
				if(pos % 2 == 0)
				{
					avValSpin.setVisibility(View.VISIBLE);
					avValText.setVisibility(View.GONE);
				}
				else {
					avValSpin.setVisibility(View.GONE);
					avValText.setVisibility(View.VISIBLE);
				}
			}*/
		
			
			for(int k=5;k<fields.size()-1&&k<12;k++){
				//5
				String metaValue="";
				metas.trimToSize();
				for(int t=metas.size()-1;t>=0;t--){
					
						if(fields.get(k).fieldName.equalsIgnoreCase(metas.get(t).metaKey)){
							metaValue=metas.get(t).getMetaValue();
							break;
						}
					
				}
				String[] avListString = new String[1];
				avListString[0] = metaValue;
				ArrayAdapter<String> avAdapter = new ArrayAdapter<String>(Show_AV.this,
						android.R.layout.simple_spinner_item, avListString);
				
				if( k==5 && fields.get(5).getFieldType().equalsIgnoreCase("drop-down")){
				/*	String string = fields.get(5).getFieldValues();
					String[] parts = string.split(",");
					
					ArrayAdapter<String> avAdapter = new ArrayAdapter<String>(Show_AV.this,
							android.R.layout.simple_spinner_item, parts);*/
					textView02.setVisibility(View.VISIBLE);
					textView02.setText(fields.get(5).fieldLabel);
					avValSpin.setAdapter(avAdapter);
					avValSpin.setSelection(0, true);
					//
					avValSpin.setVisibility(View.VISIBLE);
					avValSpin.setTag(fields.get(5).getFieldName());
					avValText.setVisibility(View.GONE);
					
				
				}else if(k==5 && (fields.get(5).getFieldType().equalsIgnoreCase("text area")||fields.get(5).getFieldType().equalsIgnoreCase("text box"))){
					textView02.setVisibility(View.VISIBLE);
					textView02.setText(fields.get(5).fieldLabel);
					avValSpin.setVisibility(View.GONE);
					avValText .setVisibility(View.VISIBLE);
					avValText.setText(metaValue);
				}
				//6
				
				if( k==6 && fields.get(6).getFieldType().equalsIgnoreCase("drop-down")){
					/*String string = fields.get(6).getFieldValues();
					String[] parts = string.split(",");
					
					ArrayAdapter<String> avAdapter = new ArrayAdapter<String>(Show_AV.this,
							android.R.layout.simple_spinner_item, parts);*/
					textView03.setVisibility(View.VISIBLE);
					textView03.setText(fields.get(6).fieldLabel);
					avValSpin03.setAdapter(avAdapter);
					avValSpin03.setSelection(0, true);
					//
					avValSpin03.setVisibility(View.VISIBLE);
					avValSpin03.setTag(fields.get(6).getFieldName());
					avValText03.setVisibility(View.GONE);
				}else if(k==6 && (fields.get(6).getFieldType().equalsIgnoreCase("text area")||fields.get(6).getFieldType().equalsIgnoreCase("text box"))){
					textView03.setVisibility(View.VISIBLE);
					textView03.setText(fields.get(6).fieldLabel);
					avValSpin03.setVisibility(View.GONE);
					avValText03 .setVisibility(View.VISIBLE);
					avValText03.setText(metaValue);

				}
				//7
				if( k==7 && fields.get(7).getFieldType().equalsIgnoreCase("drop-down")){
				/*	String string = fields.get(7).getFieldValues();
					String[] parts = string.split(",");
					
					ArrayAdapter<String> avAdapter = new ArrayAdapter<String>(Show_AV.this,
							android.R.layout.simple_spinner_item, parts);*/
					textView04.setVisibility(View.VISIBLE);
					textView04.setText(fields.get(7).fieldLabel);
					avValSpin04.setAdapter(avAdapter);
					avValSpin04.setSelection(0, true);

					//
					avValSpin04.setVisibility(View.VISIBLE);
					avValSpin04.setTag(fields.get(7).getFieldName());
					avValText04.setVisibility(View.GONE);
				}else if(k==7 && (fields.get(7).getFieldType().equalsIgnoreCase("text area")||fields.get(7).getFieldType().equalsIgnoreCase("text box"))){
					textView04.setVisibility(View.VISIBLE);
					textView04.setText(fields.get(7).fieldLabel);
					avValSpin04.setVisibility(View.GONE);
					avValText04 .setVisibility(View.VISIBLE);
					avValText04.setText(metaValue);

				}
			}
			

		}

}
	
	class GetAVMeta extends AsyncTask<String, String, String> {

		
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
			try {
				// getting JSON string from URL
				
				params.add(new BasicNameValuePair("post_id", Integer.toString(ID)));
				
				
				JSONObject json = jsonParser.makeHttpRequest(url_getAvMeta, "GET", params);
				// Check your log cat for JSON reponse
				//Log.d("All Products: ", json.toString());
				// Checking for SUCCESS TAG
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					
					metaList = json.getJSONArray("metaList");

					// looping through All Products
					for (int i = 0; i < metaList.length(); i++) {
						JSONObject c = metaList.getJSONObject(i);

						// Storing each json item in variable
					
						int post_id =Integer.parseInt( c.getString("post_id"));
						int metaId = Integer.parseInt( c.getString("meta_id"));
						String metaKey = c.getString("meta_key");
						String metaValue = c.getString("meta_value");
						
					
						
						metas.add(new Meta(metaId,post_id,metaKey,metaValue));
						
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
			// dismiss the dialog once done
		}
	}
	public String convertAddName (String s){

		s = s.toLowerCase();
		s = s.replace("ü", "u");
		s = s.replace("ý", "i");
		s = s.replace("ö", "o");
		s = s.replace("þ", "s");
		s = s.replace("ð", "g");
		s = s.replace("ç", "c");
		s = s.replace("ð", "g");
		return s;
	}
}
