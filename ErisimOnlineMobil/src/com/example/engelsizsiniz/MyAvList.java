package com.example.engelsizsiniz;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import org.apache.http.NameValuePair;
import org.apache.http.message.BasicNameValuePair;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import android.app.ListActivity;
import android.app.ProgressDialog;
import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.util.Log;
import android.view.Gravity;
import android.view.View;
import android.widget.AdapterView;
import android.widget.AdapterView.OnItemClickListener;
import android.widget.ArrayAdapter;
import android.widget.ListAdapter;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.TextView;
import android.widget.Toast;


 
public class MyAvList extends ListActivity {
 
	JSONParser jsonParser = new JSONParser();
	public ProgressDialog pDialog;
	String userIdDB;

	
	public static ArrayList<AV> avList = new ArrayList<AV>();
 
    // url to get all products list
    private static String url_listAV = "http://swe.cmpe.boun.edu.tr/fall2012g4/get_all_my_AV.php";
 
    // products JSONArray
    public JSONArray products = null;
    
 
    @Override
    public void onCreate(Bundle savedInstanceState) {
    	avList = new ArrayList<AV>();
    	products = null;
        super.onCreate(savedInstanceState);
        userIdDB = getIntent().getExtras().getString("id");
        setContentView(R.layout.activity_my_av_list);
        // Loading products in Background Thread
        new LoadAllProducts().execute();
    }
 
    // Response from Edit Product Activity
    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        // if result code 100
        if (resultCode == 100) {
            // if result code 100 is received
            // means user edited/deleted product
            // reload this screen again
            Intent intent = getIntent();
            finish();
            startActivity(intent);
        }
 
    }
    
    @Override
	protected void onListItemClick(ListView l, View v, int position, long id) {
		// TODO Auto-generated method stub
		Intent myIntent = new Intent(getApplicationContext(), Show_AV.class);
		myIntent.putExtra("position", position);
		startActivityForResult(myIntent, 0);
	}
	
	public String[] getArray (){
		
		String [] values = new String[avList.size()];
		
		for (int i = 0; i < avList.size(); i++)
		{
				values[i] = avList.get(i).getPost_title().toString();
		}
		
		return values;
	}
	
	public void afterExec() {
		ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, android.R.layout.simple_list_item_1, getArray());
		setListAdapter(adapter);
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
 
    /**
     * Background Async Task to Load all product by making HTTP Request
     * */
    class LoadAllProducts extends AsyncTask<String, String, String> {
 
        /**
         * Before starting background thread Show Progress Dialog
         * */
        @Override
        protected void onPreExecute() {
            super.onPreExecute();
            pDialog = new ProgressDialog(MyAvList.this);
            pDialog.setMessage("Violationlar Listeleniyor...");
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
        				params.add(new BasicNameValuePair("post_author", userIdDB));
        				try {
        					// getting JSON string from URL
        					JSONObject json = jsonParser.makeHttpRequest(url_listAV, "GET", params);
        					// Check your log cat for JSON reponse
        					//Log.d("All Products: ", json.toString());
        					// Checking for SUCCESS TAG
        					int success = json.getInt("success");

        					if (success == 1) {
        						// products found
        						// Getting Array of Products
        						products = json.getJSONArray("avler");
        						
        						// looping through All Products
        						for (int i = 0; i < products.length(); i++) {
        							JSONObject c = products.getJSONObject(i);
        							AV av = new AV();
        							System.out.println(i);
        							int parent =  c.getInt("post_parent");
        							String postTip = c.getString("post_type");
        							String guid  = c.getString("guid");
        							
        							if(parent == 0){
        								av.setID(c.getInt("ID"));
            							av.setPost_date(c.getString("post_date"));
            							av.setPost_content(c.getString("post_content"));
            							av.setPost_title(c.getString("post_title"));
            							av.setPost_author(Integer.parseInt(userIdDB));
            							av.setPost_type(postTip);
            							av.setPost_parent(parent);
            							av.setGuid("");
        								MyAvList.avList.add(av);
        							}
        							else {
        								if(postTip.equals("attachment"))
        								{
        									for(int k = 0; k < MyAvList.avList.size(); k++) {
        										if ((avList.get(k).ID == parent )){
        											avList.get(k).setGuid(guid);
        										}
        											
        									}
        								}
        							}
        						}
                } else {
                    // no products found
                    // Launch Add New product Activity
                    backMenu();
                }
            } catch (JSONException e) {
            	backMenu();
            }
        	catch (Exception e) {
        		backMenu();
        	}
        				
 
            return null;
        }
 
        /**
         * After completing background task Dismiss the progress dialog
         * **/
        protected void onPostExecute(String file_url) {
        	afterExec();
			pDialog.dismiss();
			Toast toast;
			toast = Toast.makeText(getApplicationContext(), "Lütfen AV Seçiniz", Toast.LENGTH_SHORT);
			toast.setGravity(Gravity.CENTER|Gravity.CENTER_HORIZONTAL, 0, 0);
			toast.show();
        }
 
    }
}
