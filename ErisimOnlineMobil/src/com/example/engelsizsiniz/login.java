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
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

public class login extends Activity {

	TextView notregistered;
	Button anonymous, login;
	EditText username, password;

	static int MINUSERNAME = 5;
	static int PASSMIN = 4;

	private static final String DNAME = "engelsizsiniz";
	private static File myDir = new File(Environment.getExternalStorageDirectory(), DNAME);
	private File readFile = new File(myDir,"cookie");
	
	private ProgressDialog pDialog;
	JSONParser jsonParser = new JSONParser();
	String usernameDB, passwordDB;
	
	private static boolean status = false;

	private static String url_registration = "http://swe.cmpe.boun.edu.tr/fall2012g4/userLogin.php";
	private static final String TAG_SUCCESS = "success";

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);

		//if cookie exists
		if(checkCookie()) {
			Intent myIntent = new Intent(getApplicationContext(), home.class);
			startActivityForResult(myIntent, 0);
			finish();
		}

		setContentView(R.layout.activity_login); 

		//define gui
		defineGUI();

		//define listeners
		defineButtonListeners(); 
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_login, menu);
		return true;
	}

	public boolean checkCookie () {

		if(readFile.exists()) {
			
			return true;
		}
		else{
			myDir.mkdir();
			return false;
		}
	}

	public void defineGUI() {

		//define textview
		notregistered = (TextView)findViewById (R.id.notregistered);

		//define buttons
		login = (Button) findViewById(R.id.login);
		anonymous = (Button) findViewById(R.id.anonymous);

		//define edittext
		username = (EditText) findViewById(R.id.username);
		password = (EditText) findViewById(R.id.password);

	}

	public void defineButtonListeners() {

		//login button clicked
		login.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				checkLogin();
			}

		});

		//anonymous
		anonymous.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), home.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});

		notregistered.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), registration.class);
				startActivityForResult(myIntent, 0);
				finish();
			}
		});
	}

	public boolean checkLogin(){

		//create toast to show message
		Toast toast;

		//get values
		usernameDB = this.username.getText().toString();
		passwordDB = this.password.getText().toString();

		//check values
		if (username.length() < MINUSERNAME)
		{
			toast = Toast.makeText(getApplicationContext(), "Kullanýcý adýnýz en az 5 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else if (password.length() < PASSMIN)
		{
			toast = Toast.makeText(getApplicationContext(), "Parolanýz en az 4 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else
		{
			// check login from website
			new LoginUser().execute();
			return true;
		}

	}

	public static void writecookie(String username, String id){
		File writeFile;
		writeFile = new File(myDir,"cookie");

		try {
			FileWriter filewriter = new FileWriter(writeFile);
			BufferedWriter out = new BufferedWriter(filewriter);
			out.write(username);
			out.write("\n");
			out.write(id);
			out.close();
			filewriter.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	/**
	 * Background Async Task to Create new product
	 * */
	class LoginUser extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(login.this);
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
			params.add(new BasicNameValuePair("user_pass", passwordDB));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_registration,
					"GET", params);

			// check log cat fro response
			Log.d("Login Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt(TAG_SUCCESS);
				// Getting Array of Products
				JSONArray products = json.getJSONArray("users");
				// looping through All Products
				JSONObject c = products.getJSONObject(0);
				String id = c.getString("ID");
				if (success == 1) {
					// successfully created product
					// write cookie
					writecookie(usernameDB, id);
					Intent i = new Intent(getApplicationContext(), home.class);
					startActivity(i);
					status = true;
					// closing this screen
					finish();
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
			if (! status)
			{
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Login Hatalý", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
			}
		}

	}

}
