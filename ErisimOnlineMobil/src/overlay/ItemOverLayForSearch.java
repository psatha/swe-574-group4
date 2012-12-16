package overlay;

import java.util.ArrayList;

import android.app.AlertDialog;
import android.content.Context;
import android.content.DialogInterface;
import android.graphics.Canvas;
import android.graphics.drawable.Drawable;

import com.google.android.maps.ItemizedOverlay;
import com.google.android.maps.MapController;
import com.google.android.maps.MapView;
import com.google.android.maps.OverlayItem;

public class ItemOverLayForSearch extends ItemizedOverlay {


	private ArrayList<OverlayItem> hOverlays = new ArrayList<OverlayItem>();
	private Context pcontext;
	protected MapController viewController;

	public ItemOverLayForSearch(Drawable arg0, Context context) {
		super(boundCenterBottom(arg0));
		pcontext = context;
	}


	@Override
	protected OverlayItem createItem(int i) {
		return hOverlays.get(i);
	}

	@Override
	public int size() {
		return hOverlays.size();
	}

	public void addOverlay(OverlayItem overlay) {
		hOverlays.add(overlay);
		populate();
	}
	
	@Override
	protected boolean onTap(int index) {
		OverlayItem item = hOverlays.get(index);
		AlertDialog.Builder builder = new AlertDialog.Builder(pcontext);
		builder.setMessage(item.getTitle() + " " +item.getSnippet())
		   .setCancelable(false)
		   .setPositiveButton("Detay", new DialogInterface.OnClickListener() {
		       public void onClick(DialogInterface dialog, int id) {
		    	   //detay sayfasýna yönlendirecez
		       }
		   })
		   .setNegativeButton("Kapat", new DialogInterface.OnClickListener() {
		       public void onClick(DialogInterface dialog, int id) {
		            dialog.cancel();
		       }
		   });
		AlertDialog alert = builder.create();
		alert.show();
		return true;
	}

	@Override
	public void draw(Canvas arg0, MapView arg1, boolean arg2) {
		// TODO Auto-generated method stub
		super.draw(arg0, arg1, arg2);
	}
}
