package com.example.engelsizsiniz;

import java.io.File;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import com.example.engelsizsiniz.registration.RegisterUser;

import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import android.support.v4.app.NavUtils;

public class updateProfile extends Activity {

	static int MINUSERNAME = 5;
	static int PASSMIN = 4;

	Button updateButton, back;
	EditText umail, username, oldpassword, newpassword;

	private static final String DNAME = "engelsizsiniz";
	private File myDir = new File(Environment.getExternalStorageDirectory(), DNAME);
	private File readFile = new File(myDir,"info");
	private static boolean updated = false;

	static boolean loginStat = false;

	JSONParser jsonParser = new JSONParser();
	private ProgressDialog pDialog;

	static String usernameDB, oldpasswordDB, newpasswordDB, mailDB;

	private static String url_update = "http://swe.cmpe.boun.edu.tr/fall2012g4/updateProfile.php";
	private static String url_registration = "http://swe.cmpe.boun.edu.tr/fall2012g4/userLogin.php";
	private static String url_getUser = "http://swe.cmpe.boun.edu.tr/fall2012g4/getUser.php";

	private static final String TAG_SUCCESS = "success";
	private static final String TAG_USERS = "users";
	private static final String TAG_USERNAME = "user_login";
	private static final String TAG_MAIL = "user_email";

	// products JSONArray
	JSONArray products = null;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		usernameDB = getIntent().getExtras().getString("username");
		setContentView(R.layout.activity_update_profile);
		//set gui
		defineGUI();
		//fill gui
		new GetUserData().execute();
		//set listeners
		defineButtonListeners();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_update_profile, menu);
		return true;
	}

	public void defineGUI() {

		//define buttons
		updateButton = (Button) findViewById(R.id.update);
		back = (Button) findViewById(R.id.back);

		//define edittexs
		umail = (EditText) findViewById(R.id.umail);
		oldpassword = (EditText) findViewById(R.id.oldPass);
		newpassword = (EditText) findViewById(R.id.newPass);
		username = (EditText) findViewById(R.id.username);
		username.setFocusable(false);
		username.setEnabled(false);
	}

	public void defineButtonListeners() {

		//register button clicked
		updateButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				if(update()){
					//Intent myIntent = new Intent(getApplicationContext(), home.class);
					//startActivityForResult(myIntent, 0);
				}
				else{

				}

			}

		});

		//back button clicked
		back.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), home.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});
	}

	public boolean update (){

		//create toast to show message
		Toast toast;

		//get values
		oldpasswordDB = this.oldpassword.getText().toString();
		newpasswordDB = this.newpassword.getText().toString();
		mailDB = this.umail.getText().toString();

		if (!mailDB.contains("@") || (!mailDB.contains(".") ))
		{
			toast = Toast.makeText(getApplicationContext(), "Lütfen mail adresinizi doðru giriniz", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}


		else if (oldpasswordDB.length() < PASSMIN || newpasswordDB.length() < PASSMIN )
		{
			toast = Toast.makeText(getApplicationContext(), "Parolanýz en az 4 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else
		{
			// values correct save it to external hd of android
			//writeUserData(usernameDB, mailDB, passwordDB);
			new checkLogin().execute();
			//new UpdateUser().execute();
			//login.writecookie(usernameDB, passwordDB);
			return true;
		}
	}


	class GetUserData extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(updateProfile.this);
			pDialog.setMessage("Data Getiriliyor");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
			//do nothing
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {


			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("user_login", usernameDB));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_getUser,
					"GET", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			try {
				// Checking for SUCCESS TAG
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					// products found
					// Getting Array of Products
					products = json.getJSONArray(TAG_USERS);

					// looping through All Products
					JSONObject c = products.getJSONObject(0);

					// Storing each json item in variable
					mailDB = c.getString(TAG_MAIL);
					usernameDB = c.getString(TAG_USERNAME);

					runOnUiThread(new Runnable() {
						public void run() {
							username.setText(usernameDB);
							umail.setText(mailDB);
						}
					});


					// adding HashList to ArrayList
				} else {
					// do nothing
				}
			} catch (JSONException e) {
				e.printStackTrace();
			}
			return null;
		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			pDialog.dismiss();
		}

	}




	class UpdateUser extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(updateProfile.this);
			pDialog.setMessage("Kullanýcý Güncelleniyor..");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {


			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("user_login", usernameDB));
			params.add(new BasicNameValuePair("user_pass", newpasswordDB));
			params.add(new BasicNameValuePair("user_email", mailDB));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_update,
					"POST", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					// successfully created product
					// closing this screen
					updated = true;
				} else {
					// failed to create product
				}
			} catch (JSONException e) {
				e.printStackTrace();
			}

			return null;
		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			pDialog.dismiss();
			if (updated) {
				Intent i = new Intent(getApplicationContext(), home.class);
				startActivity(i);
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "User Güncellendi", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
				finish();
			}
			else {
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Güncelleme Hatasý", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
			}
				
		}

	}

	class checkLogin extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(updateProfile.this);
			pDialog.setMessage("Kullanýcý Kontrol Ediliyor");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {


			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("user_login", usernameDB));
			params.add(new BasicNameValuePair("user_pass", oldpasswordDB));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_registration,
					"POST", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);

				if (success == 1) {
					loginStat = true;

				} else {
					// failed to create product
					//Toast toast;
					//toast = Toast.makeText(getApplicationContext(), "Login Hatalý", Toast.LENGTH_SHORT);
					//toast.show();
				}
			} catch (JSONException e) {
				e.printStackTrace();
			}

			return null;
		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			pDialog.dismiss();
			if (! loginStat)
			{
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Eski Þifre Hatalý", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
			}
			else
			{
				new UpdateUser().execute();
			}
		}

	}




}
