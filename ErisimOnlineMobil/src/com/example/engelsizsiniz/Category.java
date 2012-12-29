package com.example.engelsizsiniz;

public class Category {
	
	int id, pos;
	String name;
	String fields;
	int formId;
	public Category (int id, String name, String fields,int formId, int pos) {
		this.id = id;
		this.name = name;
		this.fields = fields;
		this.pos = pos;
		this.formId =formId;
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
	public void setFormId(int formId) {
		this.formId = formId;
	}
	
	
}
