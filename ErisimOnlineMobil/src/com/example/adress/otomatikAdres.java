package com.example.adress;

import java.io.BufferedReader;
import java.io.DataInputStream;
import java.io.FileInputStream;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.ArrayList;

import com.example.engelsizsiniz.newViolation;

import android.app.Application;
import android.content.res.AssetManager;
import android.util.Log;
import android.widget.Toast;

public class otomatikAdres {
	
	public static ArrayList<Il> iller = new ArrayList<Il>();
	public AssetManager  assetManager;
	
	
	public otomatikAdres(AssetManager mngr)
	{
		this.assetManager = mngr;
		readFile();
		System.err.println("Error:  erasdasd");
	}

	public void readFile()
	{
		System.err.println("Error: asdasdasd");
		readCities();
		//readStates();
		//readStreets();
	}


	public void getAllCityNames () {

	}

	public void getStates (String cityName) {

	}

	public void getStates (int stateKod) {

	}

	public void readCities () {
		try {
			InputStream fstream = assetManager.open("iller");
			// Get the object of DataInputStream
			DataInputStream in = new DataInputStream(fstream);
			BufferedReader br = new BufferedReader(new InputStreamReader(in));
			String strLine;
			//Read File Line By Line
			while ((strLine = br.readLine()) != null)   {
				// Print the content on the console
				String[] readed = strLine.split("&");
				iller.add(new Il(readed[0], Integer.parseInt(readed[1])));
			}
			//Close the input stream
			in.close();
		}catch (Exception e){//Catch exception if any
			System.err.println("Error: " + e.getMessage());
		}
		
	}

	public void readStates () {

	}

	public void readStreets () {

	}

}
