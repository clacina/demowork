/*---------------------------------------------------------------------------
	SearchView.h - data classes for search criteria encapsulation.
	Handles searching by:
		Workspace Name
		File Name
		File Size
		File Type
		File modified date
		File Creation date
		File owner
		File transfer date

	Chris Lacina
	Copyright Topia Technology
	June 23, 2014
---------------------------------------------------------------------------*/
#pragma once
#include "afxwin.h"
#include "afxcmn.h"

#include "../lucidity/LucidityDoc.h"
#include "TransferManagerDlg.h"


typedef enum {
    ESC_ANY,ESC_ABOUT, ESC_GREATER, ESC_LESSER
} ESearchContraintParameter;

class CSearchCriteria {
public:
    CSearchCriteria() {
        espDate = ESC_ANY;
        espSize = ESC_ANY;
        espSender = ESC_ANY;
        espType = ESC_ANY;
        pszBaseSearch = NULL;
        pszSenderCriteriaRef = NULL;
        pszFileTypeCriteria = NULL;
        pszDeviceId = NULL;
    }
    ~CSearchCriteria() {
        delete[] pszBaseSearch;
        delete[] pszSenderCriteriaRef;
		delete[] pszDeviceId;
    }

    LPWSTR pszBaseSearch;           // Topia*.*
    LPWSTR pszSenderCriteriaRef;    // user ref
    vector<CString> *pszFileTypeCriteria;     // file string
    __int64 tsSizeCriteria;         // size in bytes
    __int64 tsDateCriteria;         // date
    LPWSTR pszDeviceId;

    ESearchContraintParameter espDate, espSize, espSender, espType;

	// Wild card comparison routine
	// -handles standard Windows wildcards '?' and'*'
	// -returns 0 if no match found, 1 if match
    int wildcmp(const WCHAR *wild, const WCHAR *string) {

        const WCHAR *cp = NULL, *mp = NULL;

        while ((*string) && (*wild != L'*')) {
            if ((*wild != *string) && (*wild != L'?')) {
                return 0;
            }
            wild++;
            string++;
        }

        while (*string) {
            if (*wild == L'*') {
                if (!*++wild) {
                    return 1;
                }
                mp = wild;
                cp = string+1;
            } else if ((*wild == *string) || (*wild == L'?')) {
                wild++;
                string++;
            } else {
                wild = mp;
                string = cp++;
            }
        }

        while (*wild == L'*') {
            wild++;
        }
        return !*wild;
    }
};

typedef enum {ETA_PRIORITY, ETA_FILE, ETA_WS, ETA_BYTEMOVED,ETA_CREATED,ETA_ID,ETA_MOTIFIED,ETA_REF,ETA_FILEREF,ETA_TOTALSIZE,ETA_SPEED,ETA_TYPE,
ETA_COMPLETED,ETA_START,ETA_COMP, ETA_SIZE, ETA_ETA, ETA_STATUS, ETA_SENDER,ETA_BYTEREMAINING} ETA_Sort;

// Base class for Grid Columns
class CGridColumn {
public:
	CGridColumn(LPWSTR name,int col,int n) {
		pszName = DuplicateString(name);
		pParent = NULL;
		nColumn = col;
		nWidth = n;
		nOrientation = LVCFMT_LEFT;
		fIsVisible = true;
		eSort = ETA_PRIORITY;
	}
	~CGridColumn() {
		delete[] pszName;
	}


	// Methods------------------------------------------------------------------
	// define pure virtual function that subclasses must define for formatting
	virtual LPWSTR Format(CDisplayEntry *pEntry) = 0;

	// Attributes---------------------------------------------------------------
	LPWSTR pszName;  // header name
	int nColumn;
	int nWidth;
	int nOrientation;
	CSuperGridCtrl *pParent;
	WCHAR szData[MAX_PATH];
	bool fIsVisible;
	ETA_Sort eSort;
};


class CFileSearchEntry {
public:
    CFileSearchEntry() {
        pszSender = NULL;
        pszWorkspace = NULL;
        pszName = NULL;
        pszStatus = NULL;
        pszFileRef = NULL;
		pszDeviceId = NULL;
        tsSize = 0;
        tsDate = 0;
    }
    ~CFileSearchEntry() {
        delete[] pszSender;
        delete[] pszWorkspace;
        delete[] pszName;
        delete[] pszStatus;
        delete[] pszFileRef;
		delete[] pszDeviceId;
    }
    WCHAR szExt[200];
    int iRecipientStatus;
    LPWSTR pszSender;
    LPWSTR pszWorkspace;
    LPWSTR pszName;
    __int64 tsSize;
    __int64 tsDate;
    LPWSTR pszStatus;
    LPWSTR pszDeviceId;

    LPWSTR pszFileRef;
};

// Define Columns for Search Grid
typedef enum {ESC_ICON,ESC_NAME,ESC_WS,ESC_SIZE,ESC_DATE,ESC_EXT,ESC_SENDER,ESC_RECIP,ESC_TSTAT} ESC_SearchSort;

// Derived class for this specific Search View
class CSearchGridColumn : public CGridColumn {
public:
    CSearchGridColumn (LPWSTR name,int col,int n) : CGridColumn(name,col,n) {
        m_Sort = ESC_ICON;
        pData = NULL;
    }
    ~CSearchGridColumn() {
    }
    virtual LPWSTR Format(CFileSearchEntry *pEntry) = 0;
    ESC_SearchSort m_Sort;
    CSkootWrapper *pData;
};

// Search by File Name
class CSCName : public CSearchGridColumn {
public:
    CSCName(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_NAME;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        swprintf(szData,L"%ls",pEntry->pszName);
        return szData;        
    }
};

// Search by Workspace Name
class CSCWS : public CSearchGridColumn {
public:
    CSCWS(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_WS;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        swprintf(szData,L"%ls",pEntry->pszWorkspace);
        return szData;        
    }
};

// Search by Size
class CSCSize : public CSearchGridColumn {
public:
    CSCSize(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_SIZE;
        nOrientation = LVCFMT_RIGHT;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        swprintf(szData,L"%ls",CCharCode().ByteSizeStringW(pEntry->tsSize));
        return szData;        
    }
};

// Search by Modified Date
class CSCDate : public CSearchGridColumn {
public:
    CSCDate(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_DATE;
        nOrientation = LVCFMT_RIGHT;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        CSkootTime skt;
        skt.SetEpocOffset(pEntry->tsDate);
        swprintf(szData,L"%ls",skt.Convert());
        return szData;        
    }
};

// Search by file type
class CSCExt : public CSearchGridColumn {
public:
    CSCExt(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_EXT;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        swprintf(szData,L"%ls",pEntry->szExt);
        return szData;        
    }
};

// Search by file owner
class CSCSender : public CSearchGridColumn {
public:
    CSCSender(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_SENDER;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        swprintf(szData,L"%ls",pEntry->pszSender);
        return szData;        
    }
};

// Search by Recipient
class CSCRecip : public CSearchGridColumn {
public:
    CSCRecip(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_RECIP;
    }

    LPWSTR Format(CFileSearchEntry *pEntry) {
        switch(pEntry->iRecipientStatus) {
            case -1:
                swprintf(szData,L""); break;
            case 0 :
                swprintf(szData,L"None"); break;
            case 1 :
                swprintf(szData,L"Some"); break;
            case 2 :
                swprintf(szData,L"All"); break;
        }
        return szData;        
    }
};

// Search by file transfer status
class CSCTStat : public CSearchGridColumn {
public:
    CSCTStat(LPWSTR name,int col,int n) : CSearchGridColumn (name,col,n) {
        m_Sort = ESC_TSTAT;
    }

    LPWSTR Format(CFileSearchEntry  *pEntry) {
        swprintf(szData,L"%ls",pEntry->pszStatus);
        return szData;        
    }
};

class CSearchGrid : public CSuperGridCtrl
{
public:
    CSearchGrid();

	// Interface to parent callback
    IListActionInterface *pActionInterface;

    // Attributes
public:
    vector<CSearchGridColumn  *> Columns;
    // Operations
public:
    void InitializeGrid(void);
    void SortData(void);
    void _DeleteAll(void);
    CImageList *CreateDragImageEx(int nItem);
    BOOL m_bDrag;
    vector<LPWSTR> PendingDelete;
    // Overrides
    void OnControlLButtonDown(UINT nFlags, CPoint point, LVHITTESTINFO& ht);	
    void OnUpdateListViewItem(CTreeItem* lpItem, LV_ITEM *plvItem);
    CItemInfo* CopyData(CItemInfo* lpSrc);
    int GetIcon(const CTreeItem* pItem);
    COLORREF GetCellRGB(void);
    BOOL OnItemExpanding(CTreeItem *pItem, int iItem);
    BOOL OnItemExpanded(CTreeItem* pItem, int iItem);
    BOOL OnCollapsing(CTreeItem *pItem);
    BOOL OnItemCollapsed(CTreeItem *pItem);
    BOOL OnDeleteItem(CTreeItem* pItem, int nIndex);
    BOOL OnVkReturn(void);
    BOOL OnItemLButtonDown(LVHITTESTINFO& ht);
    // ClassWizard generated virtual function overrides
    //{{AFX_VIRTUAL(CTMGrid)
    //}}AFX_VIRTUAL
    int InitList();
    int m_iSortColumn;
    bool m_bSortAscending;

    void RemoveRow(int nPos) {
        CSuperGridCtrl::CTreeItem* pSelItem = GetTreeItem(nPos);
        if(pSelItem!=NULL)
        {	
            DeleteItemEx2(pSelItem,nPos);
        }
        //Update(nPos);
    }

    // Implementation
public:
    virtual ~CSearchGrid();
    CImageList *pParentImageList;

protected:
    // Generated message map functions
protected:
    //{{AFX_MSG(CTMGrid)
    //}}AFX_MSG

    DECLARE_MESSAGE_MAP()
public:
    void SetSortArrow(const int iSortColumn, const bool bSortAscending);
    bool GetCommonControlVersion(DWORD &dwHigh, DWORD &dwLow);
    void MarkEntries(bool fMark);
protected:
    virtual BOOL OnNotify(WPARAM wParam, LPARAM lParam, LRESULT* pResult);
    void OnKeydown(NMHDR* pNMHDR, LRESULT* pResult);
};


class ISearchOperation {
public:
    ISearchOperation() {

    }
    virtual void DeleteFilesFromSearch(vector<LPWSTR> *) = 0;
};


class CSearchView : public CDialog, public IListActionInterface
{
	DECLARE_DYNAMIC(CSearchView)

public:
	CSearchView(CWnd* pParent = NULL);   // standard constructor
	virtual ~CSearchView();

    CShareSpaceDoc *pDoc;
    ISearchOperation *pOpInterface;
    vector<LPWSTR> SearchResults;  // File Refs

// Dialog Data
	enum { IDD = IDD_SEARCHVIEW };

    bool fShowAdvanced;
    vector<CString> csDocumentsTemplate;
    vector<CString> csAudioTemplate;
    vector<CString> csPictureTemplate;
    vector<CString> csWebTemplate;
    CImageList *pParentImageList;
    vector<CFileIconAssociation *> *FileIcons;

    vector<LPWSTR> SenderReferences;
    vector<LPWSTR> DeviceNames;
    vector<LPWSTR> DeviceIds;
protected:
	virtual void DoDataExchange(CDataExchange* pDX);    // DDX/DDV support

	DECLARE_MESSAGE_MAP()
public:
    CString m_FileName;
    CButton pbSearch;
    CStatic m_DateLabel;
    CStatic m_SizeLabel;
    CStatic m_TypeLabel;
    CStatic m_SenderLabel;
    CStatic m_DeviceLabel;
    CComboBox m_DateModifier;
    CComboBox m_SizeModifier;
    CComboBox m_DeviceList;
    COleDateTime m_Date;
    CString m_Size;
    CComboBox m_FileTypeList;
    CComboBox m_SenderList;
    CSearchGrid m_List;
    ESC_SearchSort m_CurSort;
    bool *m_pbShownFlag;
    CSearchCriteria m_SearchCriteria;

    void UpdateFromExternal(LPWSTR psz,bool fLaunch=false);

    afx_msg void OnBnClickedSearch();
    afx_msg void OnBnClickedAdv();
    afx_msg void OnBnClickedClose();
    virtual BOOL OnInitDialog();
protected:
    virtual void OnOK();
    virtual void OnCancel();
    void ShowAdvancedControls();
    vector<CFileSearchEntry *> Entries;
public:
    afx_msg void OnSize(UINT nType, int cx, int cy);
    void PostionControls(int cx, int cy);
    afx_msg void OnCbnSelchangeDateModifier();
    afx_msg void OnCbnSelchangeSizeModifier();
    afx_msg void OnCbnSelchangeDeviceModifier();
    afx_msg void OnLvnItemchangedList1(NMHDR *pNMHDR, LRESULT *pResult);
    afx_msg void OnLvnColumnclickList1(NMHDR *pNMHDR, LRESULT *pResult);
    afx_msg void OnHeaderItemClick(NMHDR *pNMHDR, LRESULT *pResult);
    afx_msg void OnListItemRClick(NMHDR *pNMHDR, LRESULT *pResult);
    afx_msg void OnEndDrag(NMHDR *pNMHDR, LRESULT *pResult);
    void OnGetMinMaxInfo(MINMAXINFO* lpMMI);
    void AddEntry(CFileSearchEntry *pEntry);
    void PopulateTransferList();
    void SortList();
    CStatic m_MbLabel;
    CButton m_pbMark;
    afx_msg void OnBnClickedMark();
    CStatic m_SelectedString;
    afx_msg void OnViewAdvancedsearch();
    afx_msg void OnUpdateViewAdvancedsearch(CCmdUI *pCmdUI);
    afx_msg void OnFileDeleteselected();
    afx_msg void OnUpdateFileDeleteselected(CCmdUI *pCmdUI);
    afx_msg void OnFileClose40061();
    afx_msg void OnInitMenuPopup(CMenu *pPopupMenu, UINT nIndex,BOOL bSysMenu);
	void DoSingleDeviceSearch( LPWSTR pszDeviceId );
};
