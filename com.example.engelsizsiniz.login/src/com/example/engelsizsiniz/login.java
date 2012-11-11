package com.example.engelsizsiniz;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;

import android.os.Bundle;
import android.os.Environment;
import android.app.Activity;
import android.content.Intent;
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
				if(checkLogin()){
					Intent myIntent = new Intent(getApplicationContext(), home.class);
					startActivityForResult(myIntent, 0);
					finish();
				}	
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
		String username = this.username.getText().toString();
		String password = this.password.getText().toString();

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
			// write cookie
			writecookie(username, password);
			
			// check login from website
			return true;
		}
		
	}

	public static void writecookie(String username, String password){
		File writeFile;
		writeFile = new File(myDir,"cookie");

		try {
			FileWriter filewriter = new FileWriter(writeFile);
			BufferedWriter out = new BufferedWriter(filewriter);
			out.write(username);
			out.write("\n");
			out.write(password);
			out.close();
			filewriter.close();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}

}
