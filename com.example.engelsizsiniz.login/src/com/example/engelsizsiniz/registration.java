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
import android.widget.Toast;




public class registration extends Activity {


	static int MINUSERNAME = 5;
	static int PASSMIN = 4;

	Button regButton, back;
	EditText mail, username, password;

	private static final String DNAME = "engelsizsiniz";
	private File myDir = new File(Environment.getExternalStorageDirectory(), DNAME);
	private File readFile = new File(myDir,"info");


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
		String username = this.username.getText().toString();
		String password = this.password.getText().toString();
		String mail = this.mail.getText().toString();

		//check values
		if (username.length() < MINUSERNAME)
		{
			toast = Toast.makeText(getApplicationContext(), "Kullanýcý adýnýz en az 5 karakterden oluþmalýdýr", Toast.LENGTH_SHORT);
			toast.show();
			return false;
		}

		else if (!mail.contains("@") || (!mail.contains(".") ))
		{
			toast = Toast.makeText(getApplicationContext(), "Lütfen mail adresinizi doðru giriniz", Toast.LENGTH_SHORT);
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
			// values correct save it to external hd of android
			writeUserData(username, mail, password);
			login.writecookie(username, password);
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
					Intent myIntent = new Intent(getApplicationContext(), home.class);
					startActivityForResult(myIntent, 0);
					finish();
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
}
