package com.example.engelsizsiniz;

public class Category {
	
	int id, pos;
	String name;
	String fields;
	
	public Category (int id, String name, String fields, int pos) {
		this.id = id;
		this.name = name;
		this.fields = fields;
		this.pos = pos;
	}

	public int getId() {
		return id;
	}

	public void setId(int id) {
		this.id = id;
	}

	public String getName() {
		return name;
	}

	public void setName(String name) {
		this.name = name;
	}

	public String getFields() {
		return fields;
	}

	public void setFields(String fields) {
		this.fields = fields;
	}
	
	
}
