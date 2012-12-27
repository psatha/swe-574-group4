package com.example.engelsizsiniz;

public class Comment {

	String author, date, content;
	
	public Comment (String author, String date, String content) {
		this.author = author;
		this.date = date;
		this.content = content;
	}

	public String getAuthor() {
		return author;
	}

	public void setAuthor(String author) {
		this.author = author;
	}

	public String getDate() {
		return date;
	}

	public void setDate(String date) {
		this.date = date;
	}

	public String getContent() {
		return content;
	}

	public void setContent(String content) {
		this.content = content;
	}

}
