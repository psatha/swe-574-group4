package com.example.adress;

import java.util.ArrayList;

public class Ilce {
	String ilceAdi;
	ArrayList<Mahalle> mahalleler;
	int ilKodu;
	
	public Ilce (String name, int ilKodu)
	{
		ilceAdi = name;
		mahalleler = new ArrayList<Mahalle>();
		this.ilKodu = ilKodu;
	}
	
	public void addMahalle ( Mahalle mahalle) {
		mahalleler.add(mahalle);
	}
}
