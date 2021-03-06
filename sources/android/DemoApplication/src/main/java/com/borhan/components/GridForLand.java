/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
package com.borhan.components;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

import android.app.Activity;
import android.content.Context;
import android.graphics.Bitmap;
import android.view.LayoutInflater;
import android.widget.LinearLayout;

import com.borhan.activity.R;
import com.borhan.client.types.BorhanMediaEntry;

/**
 *
 * @author sda
 */
public class GridForLand {

    private String TAG;
    private Activity activity;
    private LayoutInflater inflater;
    private LinearLayout row_grid;
    private ItemGrid itemLeft;
    private ItemGrid itemCenter;
    private ItemGrid itemRight;
    private int offset;
    private HashMap<BorhanMediaEntry, Bitmap> listBitmap;
    private List<BorhanMediaEntry> listKeys;

    public GridForLand(String TAG, Activity activity, int offset) {
        this.TAG = TAG;
        this.activity = activity;

        inflater = (LayoutInflater) activity.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        row_grid = (LinearLayout) inflater.inflate(R.layout.row_grid, null);

        itemLeft = new ItemGrid(TAG, row_grid, R.id.left_item);
        itemCenter = new ItemGrid(TAG, row_grid, R.id.center_item);
        itemRight = new ItemGrid(TAG, row_grid, R.id.right_item);

        if (listBitmap != null) {
            this.listBitmap = listBitmap;
        } else {
            this.listBitmap = new HashMap<BorhanMediaEntry, Bitmap>();
        }
        if (listKeys != null) {
            this.listKeys = listKeys;
        } else {
            this.listKeys = new ArrayList<BorhanMediaEntry>();
        }
        this.offset = offset;

    }

    public LinearLayout getRowGrid() {
        return row_grid;
    }

    public ItemGrid getLeftItemGrid() {
        return itemLeft;
    }

    public ItemGrid getCenterItemGrid() {
        return itemCenter;
    }

    public ItemGrid getRightItemGrid() {
        return itemRight;
    }

    private void addContent() {
    }

    public int getOffset() {
        return offset;
    }
}
