package com.example.engelsizsiniz;

import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.InputStreamReader;

import android.os.Bundle;
import android.os.Environment;
import android.app.Activity;
import android.content.Intent;
import android.view.Menu;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.support.v4.app.NavUtils;

public class home extends Activity {

	private static final String DNAME = "engelsizsiniz";
	private File myDir = new File(Environment.getExternalStorageDirectory(), DNAME);
	private File readFile = new File(myDir,"cookie");

	Button yeniHata, listHata, searchHata, updateProfil, logout;

	String username;

	@Override
	public void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		setContentView(R.layout.activity_home);

		//read cookie
		if(!readCookie()){
			Intent myIntent = new Intent(getApplicationContext(), login.class);
			startActivityForResult(myIntent, 0);
			finish();
		}

		//define gui
		defineGUI();

		//define listeners
		defineButtonListeners(); 
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		getMenuInflater().inflate(R.menu.activity_home, menu);
		return true;
	}

	public void defineGUI() {
		//define buttons
		yeniHata = (Button) findViewById(R.id.yenihata);
		listHata = (Button) findViewById(R.id.listhata);
		searchHata = (Button) findViewById(R.id.searchhata);
		updateProfil = (Button) findViewById(R.id.updateprofil);

		// set logout button with username
		logout = (Button) findViewById(R.id.logout);
		logout.setText("Not " + username  + " ? Logout");
	}

	public void defineButtonListeners(){

		//yeni hata button clicked
		yeniHata.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				Intent myIntent = new Intent(getApplicationContext(), newViolation.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});
		
		//logout button clicked
		logout.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				deleteCookie();
				Intent myIntent = new Intent(getApplicationContext(), login.class);
				startActivityForResult(myIntent, 0);
				finish();
			}

		});
	}

	public void deleteCookie()
	{
		readFile.delete();
	}

	public boolean readCookie()
	{
		// command line parameter
		FileInputStream fstream;
		try {
			fstream = new FileInputStream(readFile);
			// Get the object of DataInputStream
			DataInputStream in = new DataInputStream(fstream);
			BufferedReader br = new BufferedReader(new InputStreamReader(in));
			//Read File Line By Line
			username = br.readLine();
			fstream.close();
			in.close();
			br.close();
			return true;
		} catch (FileNotFoundException e) {
			return false;
		} catch (IOException e) {
			return false;
		}

	}

}
