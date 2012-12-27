package com.example.engelsizsiniz;


import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Date;
import java.util.List;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.os.AsyncTask;
import android.os.Bundle;
import android.app.Activity;
import android.app.Dialog;
import android.app.ProgressDialog;
import android.content.Intent;
import android.graphics.Color;
import android.util.Log;
import android.view.Gravity;
import android.view.Menu;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;

public class ShowComment extends Activity {

	private Button addComment;
	private LinearLayout scrollLayout;
	private EditText comment;

	public ProgressDialog pDialog;

	private static String url_newComment = "http://swe.cmpe.boun.edu.tr/fall2012g4/newComment.php";
	private static String url_allComments = "http://swe.cmpe.boun.edu.tr/fall2012g4/listComments.php";
	JSONParser jsonParser = new JSONParser();
	public String postID;

	public static boolean commentAdded = false;
	public static boolean commentGet = false;
	public static SimpleDateFormat ft;
	public static Date date;

	public static ArrayList<Comment> comments;

	@Override
	protected void onCreate(Bundle savedInstanceState) {
		super.onCreate(savedInstanceState);
		comments = new ArrayList<Comment>();
		postID = Integer.toString(getIntent().getExtras().getInt("id"));
		new listComments().execute();
		setContentView(R.layout.activity_show_comment);
		defineGUI();
		defineListeners();
	}

	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.activity_show_comment, menu);
		return true;
	}

	public void defineGUI() {
		addComment = (Button) findViewById(R.id.newComment);
		scrollLayout = (LinearLayout) findViewById(R.id.commentLayout);
		comment = (EditText) findViewById(R.id.commentText);

	}
	
	public void addComment(String isim, String tarih, String comment) {
		EditText temp = new EditText(getApplicationContext());
		temp.setText("Ýsim: " + isim + "\t Tarih: "  + tarih + "\n\n" + comment);
		temp.setEnabled(false);
		if (comments.size() % 2 == 0)
			temp.setBackgroundColor(Color.LTGRAY);
		comments.add(new Comment(isim, tarih, comment));
		scrollLayout.addView(temp, scrollLayout.getChildCount()-2);
		this.comment.setText("");
	}

	public void addComments()
	{
		//comments.size() - 1
		if(comments.size() == 0) {
			Toast toast;
			toast = Toast.makeText(getApplicationContext(), "Daha önce hiç yorum yapýlmadý. Ýlk Yorum Yazan Sen Ol", Toast.LENGTH_SHORT);
			toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
			toast.show();
		}
		else {
			for(int i = comments.size() - 1; i >= 0 ; i--) {
				EditText temp = new EditText(getApplicationContext());
				temp.setText("Ýsim: " + comments.get(i).getAuthor() + "\t Tarih: " + comments.get(i).getDate() + "\n\n" + comments.get(i).getContent());
				temp.setEnabled(false);
				if (i % 2 == 0)
					temp.setBackgroundColor(Color.LTGRAY);
				scrollLayout.addView(temp, 0);
			}
		}
	}

	public void defineListeners() {

		addComment.setOnClickListener(new View.OnClickListener() {
			public void onClick(View view) {
				// comment eklemece
				if(comment.getText().length() > 0)
				{
					new NewComment().execute();
				}
				
				else {
					Toast toast;
					toast = Toast.makeText(getApplicationContext(), "Lütfen Yorum Alanýný Boþ Býrakmayýn", Toast.LENGTH_SHORT);
					toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
					toast.show();
				}
			}

		});

	}

	/**
	 * Background Async Task to Create new product
	 * */
	class NewComment extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(ShowComment.this);
			pDialog.setMessage("Yorum Ekleniyor..");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(false);
			pDialog.show();
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {

			date = new Date();
			ft = new SimpleDateFormat ("yyyy-MM-dd kk:mm:ss");
			System.out.println(ft.format(date).toString());

			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("comment_post_ID", postID));
			params.add(new BasicNameValuePair("comment_author", home.username));
			params.add(new BasicNameValuePair("comment_author_email", home.email));
			params.add(new BasicNameValuePair("comment_date", ft.format(date).toString()));
			params.add(new BasicNameValuePair("comment_date_gmt", ft.format(date).toString()));
			params.add(new BasicNameValuePair("comment_content", comment.getText().toString()));
			params.add(new BasicNameValuePair("comment_karma", "0"));
			params.add(new BasicNameValuePair("comment_approved","1"));
			params.add(new BasicNameValuePair("comment_parent", "0"));
			params.add(new BasicNameValuePair("user_id", home.id));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_newComment,
					"POST", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt("success");

				if (success == 1) {
					commentAdded = true;
					// yorum eklendi	

				} else {
					commentAdded = false;
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
			if(commentAdded){
				addComment(home.username,ft.format(date).toString(),comment.getText().toString());
				pDialog.dismiss();
			}
			else{
				pDialog.dismiss();
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Yorumunuz Eklenememiþtir. Tekrar Deneyiniz", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
			}


		}

	}


	/**
	 * Background Async Task to Create new product
	 * */
	class listComments extends AsyncTask<String, String, String> {

		/**
		 * Before starting background thread Show Progress Dialog
		 * */
		@Override
		protected void onPreExecute() {
			super.onPreExecute();
			pDialog = new ProgressDialog(ShowComment.this);
			pDialog.setMessage("Yorumlar Listeleniyor..");
			pDialog.setIndeterminate(false);
			pDialog.setCancelable(false);
			pDialog.show();
		}

		/**
		 * Creating product
		 * */
		protected String doInBackground(String... args) {

			Date date = new Date( );
			SimpleDateFormat ft = new SimpleDateFormat ("yyyy-MM-dd kk:mm:ss");
			System.out.println(ft.format(date).toString());

			// Building Parameters
			List<NameValuePair> params = new ArrayList<NameValuePair>();
			params.add(new BasicNameValuePair("comment_post_ID", postID));

			// getting JSON Object
			// Note that create product url accepts POST method
			JSONObject json = jsonParser.makeHttpRequest(url_allComments,
					"POST", params);

			// check log cat fro response
			Log.d("Create Response", json.toString());

			// check for success tag
			try {
				int success = json.getInt("success");

				if (success == 1) {

					comments.clear();
					commentGet = true;
					JSONArray products = json.getJSONArray("commentler");

					// looping through All Products
					for (int i = 0; i < products.length(); i++) {
						JSONObject c = products.getJSONObject(i);
						String name = c.getString("comment_author");
						String time = c.getString("comment_date");
						String cContent  = c.getString("comment_content");
						comments.add(new Comment(name, time, cContent));
					}
				} else {
					commentGet = true;
					// failed to create product

				}
			} catch (JSONException e) {
				commentGet = false;
			}

			return null;
		}

		/**
		 * After completing background task Dismiss the progress dialog
		 * **/
		protected void onPostExecute(String file_url) {
			// dismiss the dialog once done
			if(commentGet) {
				addComments();
				pDialog.dismiss();
			}
			else{
				pDialog.dismiss();
				Toast toast;
				toast = Toast.makeText(getApplicationContext(), "Yorumunuz Listelenememiþtir. Tekrar Deneyiniz", Toast.LENGTH_SHORT);
				toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
				toast.show();
				finish();
			}
		}

	}

}
