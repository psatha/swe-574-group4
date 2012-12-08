package com.example.engelsizsiniz;


import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONException;
import org.json.JSONObject;


import android.os.AsyncTask;
import android.os.Bundle;
import android.os.Environment;
import android.app.Activity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.util.Log;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;




public class registration extends Activity {


	static int MINUSERNAME = 5;
	static int PASSMIN = 4;

	Button regButton, back;
	EditText mail, username, password;

	private static final String DNAME = "engelsizsiniz";
	private File myDir = new File(Environment.getExternalStorageDirectory(), DNAME);
	private File readFile = new File(myDir,"info");

	JSONParser jsonParser = new JSONParser();
	private ProgressDialog pDialog;
	
	String usernameDB, passwordDB, mailDB;
	
	private static String url_registration = "http://swe.cmpe.boun.edu.tr/fall2012g4/registration.php";
	private static final String TAG_SUCCESS = "success";

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		//check if user already registered
		/*
		if (checkRegistration()) {
			Intent myIntent = new Intent(getApplicationContext(), home.class);
			startActivityForResult(myIntent, 0);
			finish();
		}*/

		setContentView(R.layout.activity_registration);
		//set gui
		defineGUI();
		//set listeners
		defineButtonListeners();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_registration, menu);
		return true;
	}

	public boolean checkRegistration() {

		if(readFile.exists()) {
			return true;
		}
		else{
			myDir.mkdir();
			return false;
		}
	}

	public boolean register (){

		//create toast to show message
		Toast toast;

		//get values
		usernameDB = this.username.getText().toString();
		passwordDB = this.password.getText().toString();
		mailDB = this.mail.getText().toString();

		//check values
		if (usernameDB.length() < MINUSERNAME)
		{
			toast = Toast.makeText(getApplicationContext(), "Kullanýcý adýnýz en az 5 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else if (!mailDB.contains("@") || (!mailDB.contains(".") ))
		{
			toast = Toast.makeText(getApplicationContext(), "Lütfen mail adresinizi doðru giriniz", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}


		else if (passwordDB.length() < PASSMIN)
		{
			toast = Toast.makeText(getApplicationContext(), "Parolanýz en az 4 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else
		{
			// values correct save it to external hd of android
			new RegisterUser().execute();
			return true;
		}
	}

	public void defineGUI() {

		//define buttons
		regButton = (Button) findViewById(R.id.register);
		back = (Button) findViewById(R.id.back);

		//define edittexs
		mail = (EditText) findViewById(R.id.mail);
		password = (EditText) findViewById(R.id.password);
		username = (EditText) findViewById(R.id.username);

	}

	public void defineButtonListeners() {

		//register button clicked
		regButton.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				if(register()){
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
				Intent myIntent = new Intent(getApplicationContext(), login.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});
	}

	public void writeUserData(String username, String mail, String password){

		File writeFile;
		writeFile = new File(myDir,"info");

		try {
			FileWriter filewriter = new FileWriter(writeFile);
			BufferedWriter out = new BufferedWriter(filewriter);
			out.write(username);
			out.write("\n");
			out.write(mail);
			out.write("\n");
			out.write(password);
			out.close();
			filewriter.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}


	/**
	 * Background Async Task to Create new product
	 * */
	class RegisterUser extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(registration.this);
			pDialog.setMessage("Kullanýcý Kaydediliyor..");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(true);
			pDialog.show();
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {

			Date date = new Date( );
		    SimpleDateFormat ft = new SimpleDateFormat ("yyyy-MM-dd hh:mm:ss");
		    System.out.println(ft.format(date).toString());
			
			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("user_login", usernameDB));
			params.add(new BasicNameValuePair("user_pass", passwordDB));
			params.add(new BasicNameValuePair("user_nicename", usernameDB));
			params.add(new BasicNameValuePair("user_email", mailDB));
			params.add(new BasicNameValuePair("user_registered", ft.format(date).toString()));
			params.add(new BasicNameValuePair("user_status", "0"));
			params.add(new BasicNameValuePair("display_name", usernameDB));
			
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
					// successfully created product
					//writeUserData(usernameDB, mailDB, passwordDB);
					//login.writecookie(usernameDB, passwordDB);
					Intent i = new Intent(getApplicationContext(), login.class);
					startActivity(i);

					// closing this screen
					finish();
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
		}

	}
}
