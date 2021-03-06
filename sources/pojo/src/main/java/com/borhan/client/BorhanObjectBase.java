// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Borhan Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Borhan Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================
package com.borhan.client;

import java.io.Serializable;
import java.util.HashMap;

import org.w3c.dom.Element;
import org.w3c.dom.Node;
import org.w3c.dom.NodeList;

import com.borhan.client.types.BorhanListResponse;
import com.borhan.client.utils.ParseUtils;

/**
 * Ancestor class for all of the generated classes in the com.borhan.client.types package.
 * 
 * @author jpotts
 *
 */
@SuppressWarnings("serial")
public class BorhanObjectBase implements Serializable {
	private HashMap<String, BorhanListResponse> relatedObjects;

    public BorhanObjectBase() {
    }
    
    public BorhanObjectBase(Element node) throws BorhanApiException {
        NodeList childNodes = node.getChildNodes();
        for (int i = 0; i < childNodes.getLength(); i++) {
            Node aNode = childNodes.item(i);
            String nodeName = aNode.getNodeName();
            if (nodeName.equals("relatedObjects")) {
                this.relatedObjects = ParseUtils.parseMap(BorhanListResponse.class, aNode);
            } 
        }
    }
    
	public BorhanParams toParams() throws BorhanApiException {
		return new BorhanParams();
	}

    public HashMap<String, BorhanListResponse> getRelatedObjects(){
        return this.relatedObjects;
    }
    
    public void setRelatedObjects(HashMap<String, BorhanListResponse> relatedObjects){
        this.relatedObjects = relatedObjects;
    }
}
