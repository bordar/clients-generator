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
using System;
using System.Text;
using System.Net;

namespace Borhan
{
    public class BorhanConfiguration
    {
        #region Private Fields

        private string _ServiceUrl = "http://www.borhan.com/";
        private EBorhanServiceFormat _ServiceFormat = EBorhanServiceFormat.RESPONSE_TYPE_XML;
        private IBorhanLogger _Logger;
        private int _Timeout = 120000;
		private string _ProxyAddress = "";
		private string _ProxyUser = null;
        private string _ProxyPassword = null;
        private WebHeaderCollection _RequestHeaders; 

        #endregion

        #region Properties

        public string ServiceUrl
        {
            set { _ServiceUrl = value; }
            get { return _ServiceUrl; }
        }

        public EBorhanServiceFormat ServiceFormat
        {
            get { return _ServiceFormat; }
        }

        public IBorhanLogger Logger
        {
            set { _Logger = value;  }
            get { return _Logger; }
        }

        public int Timeout
        {
            set { _Timeout = value; }
            get { return _Timeout; }
        }

		public string ProxyAddress 
		{
			set { _ProxyAddress = value; }
			get { return _ProxyAddress; }
		}

        public string ProxyUser
        {
            set { _ProxyUser = value; }
            get { return _ProxyUser; }
        }
        public string ProxyPassword
        {
            set { _ProxyPassword = value; }
            get { return _ProxyPassword; }
        }
        public WebHeaderCollection RequestHeaders
        {
            set { _RequestHeaders = value; }
            get { return _RequestHeaders; }
        }

        #endregion

        #region CTor

        /// <summary>
        /// Constructs new borhan configuration object, expecting partner id
        /// </summary>
        /// <param name="partnerId"></param>
        public BorhanConfiguration()
        {
            this._RequestHeaders = new WebHeaderCollection();
        } 

        #endregion
    }
}
