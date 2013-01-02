package com.example.engelsizsiniz;

public class Meta {
int metaId;
int postId;
String metaKey;
String metaValue;

public Meta(int metaId, int postId,String metaKey,String metaValue){
	this.metaId=metaId;
	this.postId=postId;
	this.metaValue=metaValue;
	this.metaKey=metaKey;
	
}
public int getMetaId() {
	return metaId;
}
public void setMetaId(int metaId) {
	this.metaId = metaId;
}
public int getPostId() {
	return postId;
}
public void setPostId(int postId) {
	this.postId = postId;
}
public String getMetaKey() {
	return metaKey;
}
public void setMetaKey(String metaKey) {
	this.metaKey = metaKey;
}
public String getMetaValue() {
	return metaValue;
}
public void setMetaValue(String metaValue) {
	this.metaValue = metaValue;
}
}
