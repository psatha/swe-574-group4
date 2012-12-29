package com.example.engelsizsiniz;

public class Field {
	int fieldId;
    String fieldLabel;
    String fieldType;
    String fieldValues;
    String fieldTooltip;
    boolean fieldRequiered;
	int fieldMinValue;
	int fieldMaxValue;
	int fieldMinLength;
	
	public Field (  String fieldLabel,String fieldType, String fieldValues,String fieldTooltip)//int fieldMinValue, int fieldMaxValue, int fieldMinLength ) 
	{
	//	this.fieldId = fieldId;
		this.fieldLabel = fieldLabel;
		this.fieldType = fieldType;
		this.fieldValues = fieldValues;
		this.fieldTooltip = fieldTooltip;
	//	this.fieldMinValue = fieldMinValue;
	//	this.fieldMaxValue =fieldMaxValue;
	//	this.fieldMinLength =fieldMinLength;
	}
	
	public int getFieldId() {
		return fieldId;
	}
	public void setFieldId(int fieldId) {
		this.fieldId = fieldId;
	}
	public String getFieldLabel() {
		return fieldLabel;
	}
	public void setFieldLabel(String fieldLabel) {
		this.fieldLabel = fieldLabel;
	}
	public String getFieldType() {
		return fieldType;
	}
	public void setFieldType(String fieldType) {
		this.fieldType = fieldType;
	}
	public String getFieldValues() {
		return fieldValues;
	}
	public void setFieldValues(String fieldValues) {
		this.fieldValues = fieldValues;
	}
	public String getFieldTooltip() {
		return fieldTooltip;
	}
	public void setFieldTooltip(String fieldTooltip) {
		this.fieldTooltip = fieldTooltip;
	}
	public boolean isFieldRequiered() {
		return fieldRequiered;
	}
	public void setFieldRequiered(boolean fieldRequiered) {
		this.fieldRequiered = fieldRequiered;
	}
	public int getFieldMinValue() {
		return fieldMinValue;
	}
	public void setFieldMinValue(int fieldMinValue) {
		this.fieldMinValue = fieldMinValue;
	}
	public int getFieldMaxValue() {
		return fieldMaxValue;
	}
	public void setFieldMaxValue(int fieldMaxValue) {
		this.fieldMaxValue = fieldMaxValue;
	}
	public int getFieldMinLength() {
		return fieldMinLength;
	}
	public void setFieldMinLength(int fieldMinLength) {
		this.fieldMinLength = fieldMinLength;
	}
	
	
}
