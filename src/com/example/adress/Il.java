package com.example.adress;

import java.util.ArrayList;

public class Il {
	
	String ilAdi;
	ArrayList<Ilce> ilceler;
	int kod;
	
	public Il (String name, int ilKodu)
	{
		ilAdi = name;
		ilceler = new ArrayList<Ilce>();
		this.kod = ilKodu;
	}
	
	public void addIlce (Ilce ilce) {
		ilceler.add(ilce);
	}

	public String getIlAdi() {
		return ilAdi;
	}

	public void setIlAdi(String ilAdi) {
		this.ilAdi = ilAdi;
	}

	public ArrayList<Ilce> getIlceler() {
		return ilceler;
	}

	public void setIlceler(ArrayList<Ilce> ilceler) {
		this.ilceler = ilceler;
	}

	public int getKod() {
		return kod;
	}

	public void setKod(int kod) {
		this.kod = kod;
	}
	
	

}
