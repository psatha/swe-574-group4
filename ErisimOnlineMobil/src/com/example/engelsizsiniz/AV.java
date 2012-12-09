package com.example.engelsizsiniz;

import android.content.Intent;
import android.view.Gravity;
import android.widget.Toast;

public class AV {
	
	public AV(){}
	
	int ID, post_author, post_parent;
	String post_date, post_content, post_title, guid, post_type;
	
	public AV (int ID, int post_author, int post_parent, String post_date, String post_content, String post_title, String guid, String post_type)
	{
		this.ID = ID;
		this.post_author = post_author;
		this.post_date = post_date;
		this.post_content = post_content;
		this.post_title = post_title;
		this.post_parent = post_parent;
		this.guid = guid;
		this.post_type = post_type;
	}

	public int getID() {
		return ID;
	}

	public int getPost_parent() {
		return post_parent;
	}

	public void setPost_parent(int post_parent) {
		this.post_parent = post_parent;
	}

	public String getGuid() {
		return guid;
	}

	public void setGuid(String guid) {
		this.guid = guid;
	}

	public String getPost_type() {
		return post_type;
	}

	public void setPost_type(String post_type) {
		this.post_type = post_type;
	}

	public void setID(int iD) {
		ID = iD;
	}

	public int getPost_author() {
		return post_author;
	}

	public void setPost_author(int post_author) {
		this.post_author = post_author;
	}

	public String getPost_date() {
		return post_date;
	}

	public void setPost_date(String post_date) {
		this.post_date = post_date;
	}

	public String getPost_content() {
		return post_content;
	}

	public void setPost_content(String post_content) {
		this.post_content = post_content;
	}

	public String getPost_title() {
		return post_title;
	}

	public void setPost_title(String post_title) {
		this.post_title = post_title;
	}
	
	
	
	

}
