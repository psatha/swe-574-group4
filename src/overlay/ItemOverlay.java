package overlay;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

import android.app.AlertDialog;
import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Paint;
import android.graphics.drawable.Drawable;
import android.location.Address;
import android.location.Geocoder;
import android.view.MotionEvent;
import android.widget.TextView;
import android.widget.Toast;

import com.example.engelsizsiniz.R;
import com.example.engelsizsiniz.newViolation;
import com.google.android.maps.GeoPoint;
import com.google.android.maps.ItemizedOverlay;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.Overlay;
import com.google.android.maps.OverlayItem;


public class ItemOverlay extends ItemizedOverlay {

	private ArrayList<OverlayItem> mOverlays = new ArrayList<OverlayItem>();
	private Context mcontext;
	protected MapController mapController;
	static long touchTime = 0;
	static long touchDuration = 0;



	public ItemOverlay(Drawable arg0, Context context) {
		super(boundCenterBottom(arg0));
		mcontext = context;
	}

	@Override
	protected OverlayItem createItem(int i) {
		return mOverlays.get(i);
	}

	@Override
	public int size() {
		return mOverlays.size();
	}

	public void addOverlay(OverlayItem overlay) {
		mOverlays.add(overlay);
		populate();
	}


	@Override
	public boolean onTouchEvent(MotionEvent event, MapView mapView) {

		if ( event.getAction() == MotionEvent.ACTION_DOWN )
		{
			//Start timer
			touchTime = System.currentTimeMillis();
			System.out.println("basssss " + touchTime );


		}else if ( event.getAction() == MotionEvent.ACTION_UP )
		{
			//stop timer
			System.out.println(System.currentTimeMillis());
			touchDuration = System.currentTimeMillis() - touchTime;
			System.out.println(touchDuration);

			if ( touchDuration > 300 ){
				if (mOverlays.size() != 0)
				{
					mOverlays.removeAll(mOverlays);
				}

				GeoPoint p = mapView.getProjection().fromPixels(
						(int) event.getX(),
						(int) event.getY());

				Geocoder geocoder = new Geocoder(mapView.getContext(), Locale.getDefault());
				try {
					Address add = geocoder.getFromLocation(p.getLatitudeE6() / 1E6, p.getLongitudeE6() / 1E6, 1).get(0);
					//adres.setText(add.getAddressLine(0) + " " + add.getAddressLine(1) + " " + add.getAddressLine(2));
					//String result = districtName + " -- " + streetName + " -- " + cityName + "  --";
					addOverlay(new OverlayItem(p, "Bulunduðunuz Adres" , add.getAddressLine(0) + " " + add.getAddressLine(1) + " " + add.getAddressLine(2)));
					TextView txtView = (TextView) ((newViolation)mcontext).findViewById(R.id.bulunduguAdres);
					txtView.setText(add.getAddressLine(0) + " " + add.getAddressLine(1) + " " + add.getAddressLine(2));
				} catch (IOException e) {
					e.printStackTrace();
				}
				mapView.invalidate();
			}
		}

		
		return false;	// TODO Auto-generated method stub*/
	}


	@Override
	protected boolean onTap(int index) {
		OverlayItem item = mOverlays.get(index);
		AlertDialog.Builder dialog = new AlertDialog.Builder(mcontext);
		dialog.setTitle(item.getTitle());
		dialog.setMessage(item.getSnippet());
		dialog.show();
		return true;
	}

	@Override
	public void draw(Canvas arg0, MapView arg1, boolean arg2) {
		// TODO Auto-generated method stub
		super.draw(arg0, arg1, arg2);
	}
}
