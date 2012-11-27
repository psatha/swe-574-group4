package com.example.wampdeneme;

import android.os.Bundle;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.view.Menu;

public class MainScreenActivity extends Activity {
	Button btnViewProducts;
    Button btnNewProduct;
    @Override
    public void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.main_screen);
     // Buttons
        btnViewProducts = (Button) findViewById(R.id.btnViewProducts);
        btnNewProduct = (Button) findViewById(R.id.btnCreateProduct);
     // view products click event
        btnViewProducts.setOnClickListener(new View.OnClickListener() {
			
			public void onClick(View v) {
				// TODO Auto-generated method stub
				 Intent i = new Intent(getApplicationContext(), AllProductsActivity.class);
	                startActivity(i);
			}
		});
     // view products click event
        btnNewProduct.setOnClickListener(new View.OnClickListener() {
			
			public void onClick(View v) {
				// TODO Auto-generated method stub
				 // Launching create new product activity
                Intent i = new Intent(getApplicationContext(), NewProductActivity.class);
                startActivity(i);
			}
		});
    }

    @Override
    public boolean onCreateOptionsMenu(Menu menu) {
        getMenuInflater().inflate(R.menu.activity_main_screen, menu);
        return true;
    }
}
